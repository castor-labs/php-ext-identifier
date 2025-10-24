const std = @import("std");

pub fn build(b: *std.Build) void {
    // Build step using zig cc to compile the extension
    const build_step = b.step("build", "Build the PHP extension");

    const compile_cmd = b.addSystemCommand(&[_][]const u8{
        "zig", "cc",
        "-shared",
        "-fPIC",
        "-DCOMPILE_DL_IDENTIFIER",
        "-DHAVE_CONFIG_H",
        "-std=c99",
        "-I.", // Include current directory for config.h
        "-I/usr/include/php/20210902",
        "-I/usr/include/php/20210902/main",
        "-I/usr/include/php/20210902/TSRM",
        "-I/usr/include/php/20210902/Zend",
        "-I/usr/include/php/20210902/ext",
        "-I/usr/include/php/20210902/ext/date/lib",
        "-Wno-unicode", // Suppress unicode warnings
        "-o", "modules/identifier.so",
        "src/php_identifier.c",
        "src/bit128.c",
        "src/codec.c",
        "src/context.c",
        "src/context_fixed.c",
        "src/context_system.c",
        "src/ulid.c",
        "src/uuid.c",
        "src/uuid_version1.c",
        "src/uuid_version3.c",
        "src/uuid_version4.c",
        "src/uuid_version5.c",
        "src/uuid_version6.c",
        "src/uuid_version7.c",
        "-lm",
    });

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
    const install_cmd = b.addSystemCommand(&[_][]const u8{
        "cp", "modules/identifier.so", "/usr/lib/php/20210902/",
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


}
