const std = @import("std");

/// Get PHP include paths from php-config at build time
fn getPhpIncludes(allocator: std.mem.Allocator) ![]const []const u8 {
    const result = try std.process.Child.run(.{
        .allocator = allocator,
        .argv = &[_][]const u8{ "php-config", "--includes" },
    });
    defer allocator.free(result.stdout);
    defer allocator.free(result.stderr);

    if (result.term.Exited != 0) {
        return error.PhpConfigFailed;
    }

    // Parse the output which is in format: -I/path1 -I/path2 -I/path3
    var includes = try std.ArrayList([]const u8).initCapacity(allocator, 6);
    errdefer includes.deinit(allocator);

    var iter = std.mem.tokenizeScalar(u8, result.stdout, ' ');
    while (iter.next()) |token| {
        const trimmed = std.mem.trim(u8, token, &std.ascii.whitespace);
        if (std.mem.startsWith(u8, trimmed, "-I")) {
            try includes.append(allocator, try allocator.dupe(u8, trimmed));
        }
    }

    return try includes.toOwnedSlice(allocator);
}

/// Get PHP extension directory from php-config at build time
fn getPhpExtensionDir(allocator: std.mem.Allocator) ![]const u8 {
    const result = try std.process.Child.run(.{
        .allocator = allocator,
        .argv = &[_][]const u8{ "php-config", "--extension-dir" },
    });
    defer allocator.free(result.stderr);

    if (result.term.Exited != 0) {
        allocator.free(result.stdout);
        return error.PhpConfigFailed;
    }

    return std.mem.trim(u8, result.stdout, &std.ascii.whitespace);
}

/// Discover all .c files in a directory
fn discoverSourceFiles(allocator: std.mem.Allocator, dir_path: []const u8) ![]const []const u8 {
    var source_files = std.ArrayList([]const u8).initCapacity(allocator, 16) catch @panic("OOM");
    errdefer source_files.deinit(allocator);

    var dir = try std.fs.cwd().openDir(dir_path, .{ .iterate = true });
    defer dir.close();

    var iterator = dir.iterate();
    while (try iterator.next()) |entry| {
        if (entry.kind == .file and std.mem.endsWith(u8, entry.name, ".c")) {
            const full_path = try std.fmt.allocPrint(
                allocator,
                "{s}/{s}",
                .{ dir_path, entry.name },
            );
            try source_files.append(allocator, full_path);
        }
    }

    return try source_files.toOwnedSlice(allocator);
}

pub fn build(b: *std.Build) void {
    // Build step using zig cc to compile the extension
    const build_step = b.step("build", "Build the PHP extension");

    // Get PHP include paths dynamically
    const php_includes = getPhpIncludes(b.allocator) catch |err| {
        std.debug.print("Error getting PHP includes: {}\n", .{err});
        std.debug.print("Make sure 'php-config' is available in your PATH\n", .{});
        std.process.exit(1);
    };

    // Discover source files dynamically
    const source_files = discoverSourceFiles(b.allocator, "src") catch |err| {
        std.debug.print("Error discovering source files: {}\n", .{err});
        std.process.exit(1);
    };

    // Build compilation command with dynamic includes
    var compile_args = std.ArrayList([]const u8).initCapacity(b.allocator, 32) catch @panic("OOM");
    compile_args.appendSlice(b.allocator, &[_][]const u8{
        "zig", "cc",
        "-shared",
        "-fPIC",
        "-DCOMPILE_DL_IDENTIFIER",
        "-DHAVE_CONFIG_H",
        "-std=c99",
        "-I.", // Include current directory for config.h
    }) catch @panic("OOM");

    // Add dynamic PHP include paths
    compile_args.appendSlice(b.allocator, php_includes) catch @panic("OOM");

    // Add remaining compilation flags
    compile_args.appendSlice(b.allocator, &[_][]const u8{
        "-Wno-unicode", // Suppress unicode warnings
        "-o", "modules/identifier.so",
    }) catch @panic("OOM");

    // Add dynamically discovered source files
    compile_args.appendSlice(b.allocator, source_files) catch @panic("OOM");

    // Add linker flags
    compile_args.appendSlice(b.allocator, &[_][]const u8{
        "-lm",
    }) catch @panic("OOM");

    const compile_cmd = b.addSystemCommand(compile_args.items);

    // Create modules directory first
    const mkdir_cmd = b.addSystemCommand(&[_][]const u8{ "mkdir", "-p", "modules" });
    compile_cmd.step.dependOn(&mkdir_cmd.step);

    build_step.dependOn(&compile_cmd.step);
    b.default_step = build_step;

    // Clean step
    const clean_step = b.step("clean", "Clean build artifacts");
    const clean_cmd = b.addSystemCommand(&[_][]const u8{
        "rm", "-rf", "zig-cache", "zig-out", "modules/identifier.so", "src/*.o", "src/*.lo", "src/*.dep",
    });
    clean_step.dependOn(&clean_cmd.step);

    // Test step
    const test_step = b.step("test", "Run PHP tests");
    const test_cmd = b.addSystemCommand(&[_][]const u8{
        "php", "tools/run-tests.php", "-d", "extension=./modules/identifier.so", "tests/",
    });
    test_cmd.step.dependOn(build_step);
    test_step.dependOn(&test_cmd.step);

    // Install step for system-wide installation
    const install_system_step = b.step("install-system", "Install extension to system PHP");

    // Get PHP extension directory dynamically
    const php_ext_dir = getPhpExtensionDir(b.allocator) catch |err| {
        std.debug.print("Error getting PHP extension directory: {}\n", .{err});
        std.debug.print("Make sure 'php-config' is available in your PATH\n", .{});
        std.process.exit(1);
    };

    const install_target = std.fmt.allocPrint(
        b.allocator,
        "{s}/identifier.so",
        .{php_ext_dir},
    ) catch @panic("OOM");

    const install_cmd = b.addSystemCommand(&[_][]const u8{
        "cp", "modules/identifier.so", install_target,
    });
    install_cmd.step.dependOn(build_step);
    install_system_step.dependOn(&install_cmd.step);

    // Development step (debug build + test)
    const dev_step = b.step("dev", "Development build and test");
    dev_step.dependOn(build_step);
    dev_step.dependOn(test_step);

    // Stub generation step (always with full documentation)
    const stubs_step = b.step("generate-stubs", "Generate PHP stubs with full documentation");
    const stubs_cmd = b.addSystemCommand(&[_][]const u8{
        "php", "-d", "extension=./modules/identifier.so",
        "tools/generate-stubs.php", "identifier", "src", "stubs/identifier_gen.stub.php",
    });
    stubs_cmd.step.dependOn(build_step);
    stubs_step.dependOn(&stubs_cmd.step);

    // Stub verification step (compare with manual stubs)
    const verify_stubs_step = b.step("verify-stubs", "Verify manual stubs match extension API");
    const verify_cmd = b.addSystemCommand(&[_][]const u8{
        "php", "tools/verify-stubs.php", "stubs/identifier.stub.php", "stubs/identifier_gen.stub.php",
    });
    verify_cmd.step.dependOn(&stubs_cmd.step);
    verify_stubs_step.dependOn(&verify_cmd.step);

    // PECL package step
    const package_step = b.step("package", "Create PECL package");
    const package_cmd = b.addSystemCommand(&[_][]const u8{
        "pecl", "package", "package.xml",
    });
    package_step.dependOn(&package_cmd.step);

    // Benchmark steps

    // Quick benchmark (Docker-based)
    const bench_step = b.step("bench", "Run quick performance benchmarks in Docker");
    const bench_build_cmd = b.addSystemCommand(&[_][]const u8{
        "docker", "build", "-f", "bench/Dockerfile", "-t", "identifier-bench", ".",
    });
    const bench_run_cmd = b.addSystemCommand(&[_][]const u8{
        "docker", "run", "--rm", "-v", "./bench/results:/app/bench/results", "identifier-bench",
    });
    bench_build_cmd.step.dependOn(build_step);
    bench_run_cmd.step.dependOn(&bench_build_cmd.step);
    bench_step.dependOn(&bench_run_cmd.step);

    // Full benchmark analysis (Docker-based)
    const bench_full_step = b.step("bench-full", "Run comprehensive PHPBench analysis in Docker");
    const bench_full_build_cmd = b.addSystemCommand(&[_][]const u8{
        "docker", "build", "-f", "bench/Dockerfile", "-t", "identifier-bench", ".",
    });
    const bench_full_run_cmd = b.addSystemCommand(&[_][]const u8{
        "docker", "run", "--rm", "-v", "./bench/results:/app/bench/results", "identifier-bench",
        "vendor/bin/phpbench", "run", "--report=default",
    });
    bench_full_build_cmd.step.dependOn(build_step);
    bench_full_run_cmd.step.dependOn(&bench_full_build_cmd.step);
    bench_full_step.dependOn(&bench_full_run_cmd.step);

    // HTML report generation (local)
    const bench_html_step = b.step("bench-html", "Open HTML benchmark report");
    const bench_html_cmd = b.addSystemCommand(&[_][]const u8{
        "echo", "📊 HTML Performance Report available at: bench/results/performance_report.html",
    });
    bench_html_step.dependOn(&bench_html_cmd.step);

    // Local benchmark (fallback)
    const bench_local_step = b.step("bench-local", "Run local performance benchmarks");
    const bench_local_cmd = b.addSystemCommand(&[_][]const u8{
        "php", "-d", "extension=./modules/identifier.so", "bench/simple/realistic_comparison.php",
    });
    bench_local_cmd.step.dependOn(build_step);
    bench_local_step.dependOn(&bench_local_cmd.step);


}
