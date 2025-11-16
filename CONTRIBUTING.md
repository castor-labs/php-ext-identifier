# Contributing to PHP Identifier Extension

Thank you for your interest in contributing to the PHP Identifier Extension! This document provides guidelines and information for contributors.

## Development Setup

### Prerequisites

- PHP 8.1 or higher with development headers (`php-config` must be available)
- Zig 0.15.2+ ([Download from ziglang.org](https://ziglang.org/download/))
- Git

### Setting Up the Development Environment

1. Clone the repository:
```bash
git clone https://github.com/castor-labs/php-ext-identifier.git
cd php-ext-identifier
```

2. Build and test the extension:
```bash
zig build dev  # Build + test in one command
```

3. Verify the extension loads:
```bash
php -d extension=./modules/identifier.so -m | grep identifier
```

## Understanding the Build System

This project uses **Zig's build system** instead of traditional PHP extension build tools (phpize/autoconf). The build system:

- Automatically discovers all `.c` files in the `src/` directory
- Uses `php-config` to get PHP include paths and extension directories
- Compiles everything into `modules/identifier.so`

Key build commands:
```bash
zig build              # Build the extension
zig build test         # Run tests
zig build dev          # Build + test
zig build clean        # Clean build artifacts
zig build generate-stubs     # Generate PHP stubs
zig build verify-stubs       # Verify stubs match API
```

## Code Architecture

### File Organization

The codebase follows a **one-file-per-class** pattern:

- `src/php_identifier.h` - Main header with all declarations
- `src/php_identifier.c` - Extension initialization and utility functions
- `src/bit128.c` - Base `Bit128` class implementation
- `src/context.c` - Context interface registration
- `src/context_system.c` - System context (uses real time/randomness)
- `src/context_fixed.c` - Fixed context (deterministic for testing)
- `src/uuid.c` - Base UUID class
- `src/uuid_version{1,3,4,5,6,7}.c` - Individual UUID implementations
- `src/ulid.c` - ULID implementation
- `src/codec.c` - Base32 encoding/decoding

### Class Hierarchy

```
Identifier\Context (interface)
  ├── Identifier\Context\System
  └── Identifier\Context\Fixed

Identifier\Bit128 (implements Stringable)
  ├── Identifier\Uuid (abstract)
  │   ├── Version1, Version3, Version4, Version5, Version6, Version7
  └── Identifier\Ulid

Identifier\Codec (static utility class)
```

## Development Workflow

### Adding a New Feature

1. **Implement in C**: Add your implementation to the appropriate `.c` file (or create a new one in `src/`)
2. **Add arginfo**: Define `ZEND_BEGIN_ARG_INFO` declarations for proper type hints
3. **Register methods**: Add methods to the class's `zend_function_entry` array
4. **Write tests**: Create `.phpt` test files in `tests/`
5. **Build and test**: Run `zig build dev`
6. **Generate stubs**: Run `zig build generate-stubs`
7. **Verify stubs**: Run `zig build verify-stubs`
8. **Update stubs**: Copy `stubs/identifier_gen.stub.php` to `stubs/identifier.stub.php`

### Writing Tests

Tests use the PHPT format:

```phpt
--TEST--
Description of what this test does
--SKIPIF--
<?php if (!extension_loaded("identifier")) print "skip"; ?>
--FILE--
<?php
// Your test code here
?>
--EXPECT--
Expected output
```

Place test files in `tests/` and run with:
```bash
zig build test  # Run all tests
php tools/run-tests.php -d extension=./modules/identifier.so tests/002-bit128.phpt  # Run single test
```

## Code Style

### C Code Style

- Follow standard PHP extension conventions
- Use 4 spaces for indentation (not tabs)
- Add descriptive comments for complex logic
- Use PHP's memory allocators (`emalloc`, `efree`, etc.) - **never** use `malloc`/`free`

### Common Patterns

**Object structure** (always embed `zend_object` as last member):
```c
typedef struct _php_identifier_myclass_obj {
    // Your data fields here
    unsigned char data[16];
    // zend_object MUST be last
    zend_object std;
} php_identifier_myclass_obj;
```

**Method implementation**:
```c
static PHP_METHOD(Identifier_MyClass, myMethod)
{
    zend_string *input;

    ZEND_PARSE_PARAMETERS_START(1, 1)
        Z_PARAM_STR(input)
    ZEND_PARSE_PARAMETERS_END();

    // Your implementation
}
```

**Error handling**:
```c
if (some_error_condition) {
    zend_throw_exception(zend_ce_exception, "Error message", 0);
    RETURN_THROWS();
}
```

### Important Guidelines

**Memory Management**:
- Always use `emalloc()`, `efree()`, `ecalloc()`, `erealloc()`
- Never mix PHP allocators with system allocators
- Handle reference counting properly for zval objects

**Random Number Generation**:
- Use `php_identifier_generate_random_bytes()` for cryptographic randomness
- Never use OS functions directly (like `/dev/urandom` or `getrandom()`)

**Timestamps**:
- Use `php_identifier_get_timestamp_ms()` for millisecond timestamps
- Use `php_identifier_get_gregorian_epoch_time()` for UUID v1/v6

**Context System**:
- All generator methods should accept optional `Context` parameter
- Use `Context\System` for real randomness
- Use `Context\Fixed` for deterministic testing

## Submitting Changes

1. Fork the repository
2. Create a feature branch: `git checkout -b feature/your-feature-name`
3. Make your changes following the workflow above
4. Ensure all tests pass: `zig build dev`
5. Commit with clear, descriptive messages
6. Push to your fork: `git push origin feature/your-feature-name`
7. Submit a pull request

### Pull Request Guidelines

- Provide a clear description of the changes
- Reference any related issues
- Ensure all tests pass
- Include new tests for new functionality
- Update stubs if API changes
- Keep commits focused and atomic

### Pull Request Title Format

Pull request titles **must** follow the [Conventional Commits](https://www.conventionalcommits.org/) specification for automated release management:

**Format**: `<type>: <description>` or `<type>(#issue): <description>`

**Allowed types**:
- `feat` - New feature (triggers minor version bump)
- `fix` - Bug fix (triggers patch version bump)
- `perf` - Performance improvement (triggers patch version bump)
- `refactor` - Code refactoring (triggers patch version bump)
- `docs` - Documentation changes only
- `style` - Code style changes (formatting, etc.)
- `test` - Adding or updating tests
- `build` - Build system changes
- `ci` - CI/CD changes
- `chore` - Other changes that don't modify src or test files
- `revert` - Reverts a previous commit

**Examples**:
- `feat: add support for UUID v8`
- `feat(#12): implement automated releases`
- `fix: resolve memory leak in ULID generation`
- `fix(#45): correct timestamp calculation`
- `perf: optimize Base32 encoding`
- `docs: update installation instructions`

**Important**: The PR title will be used to generate the release notes and determine version bumps, so make it descriptive and accurate.

## Release Process

This project uses automated releases based on semantic versioning:

### How Releases Work

1. **Automated Release Management**: When changes are merged to `main`, the semantic-release workflow analyzes PR titles to determine the version bump
2. **Version Determination**:
   - `feat` - Bumps minor version (e.g., 0.1.0 → 0.2.0)
   - `fix`, `perf`, `refactor`, `revert` - Bumps patch version (e.g., 0.1.0 → 0.1.1)
   - `BREAKING CHANGE` in PR body - Bumps major version (e.g., 0.1.0 → 1.0.0)
3. **Release Artifacts**: When a release is published:
   - Source archives are created in PIE-compliant formats
   - Binary artifacts are built for multiple platforms:
     - **Linux**: x86_64, aarch64
     - **macOS**: x86_64 (Intel), arm64 (Apple Silicon)
     - **Windows**: x64, arm64 (both TS and NTS)
   - All artifacts follow [PIE naming conventions](https://github.com/php/pie/blob/main/docs/extension-maintainers.md)
4. **CHANGELOG**: Automatically generated and updated with each release

### For Maintainers

Releases are **fully automated** - no manual intervention required:

- Merging a PR to `main` triggers the release workflow
- The workflow creates a GitHub release with proper version tag
- Build artifacts are automatically uploaded to the release
- The CHANGELOG.md is updated automatically

The first release will start at version `0.1.0` based on the initial feature commits.

## Reporting Issues

When reporting issues, please include:

- PHP version (`php -v`)
- Extension version
- Zig version (`zig version`)
- Operating system
- Minimal code example that reproduces the issue
- Expected vs actual behavior
- Any error messages or stack traces

## Getting Help

- Check the [stub file](stubs/identifier.stub.php) for API documentation
- Review existing test files in `tests/` for usage examples
- Look at existing implementations in `src/` for code patterns
- Open an issue for questions or discussions

## License

By contributing to this project, you agree that your contributions will be licensed under the MIT License.

Thank you for contributing!
