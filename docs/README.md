# Documentation

This directory contains documentation for the PHP Identifier Extension.

## Build System

The extension uses Zig as its build system. Here are the main commands:

### Basic Commands
```bash
zig build                    # Build the extension
zig build test              # Run tests
zig build dev               # Build and test
```

### Installation
```bash
zig build install-system    # Install to system PHP
```

### Packaging
```bash
zig build package          # Create PECL package (identifier-1.0.0.tgz)
```

### Stub Generation
```bash
zig build generate-stubs    # Generate PHP stubs with documentation
zig build verify-stubs      # Check if manual stubs match the API
```

## Development Workflow

1. Make changes to C source code
2. Run `zig build dev` to build and test
3. Run `zig build generate-stubs` to update stubs
4. Run `zig build verify-stubs` to check manual stubs

## Stub Generation

The extension automatically generates PHP stubs that include:
- Accurate method signatures from reflection
- Rich documentation extracted from C source comments
- Usage examples with proper formatting

### C Documentation Format
Add documentation before `PHP_METHOD` declarations:

```c
/**
 * Brief description
 * 
 * Detailed explanation of what the method does.
 * 
 * @param type $name Parameter description
 * @return type Return description
 * @throws Exception When this happens
 * 
 * @example
 * $result = Class::method($param);
 * echo $result; // Expected output
 * 
 * @since 1.0.0
 */
static PHP_METHOD(Php_Identifier_Class, method)
```

The stub generator will automatically:
- Extract this documentation
- Combine it with reflection data
- Generate properly formatted PHP stubs
- Include code examples with syntax highlighting

## Files

### Generated Files
- `stubs/identifier_gen.stub.php` - Generated stubs (always accurate)

### Manual Files  
- `stubs/identifier.stub.php` - Manual stubs (can be customized)

### Tools
- `tools/generate-stubs.php` - Stub generator
- `tools/verify-stubs.php` - Stub verifier

## Requirements

- Zig (latest stable version)
- PHP 8.1+ with development headers
- Standard build tools (make, gcc/clang)

## Troubleshooting

### Build Issues
- Ensure PHP development headers are installed
- Check that Zig can find PHP headers
- Verify PHP version is 8.1 or higher

### Stub Issues
- Run `zig build verify-stubs` to check for API drift
- Regenerate stubs with `zig build generate-stubs`
- Check C documentation format if methods aren't documented

That's it! The system is designed to be simple and automatic.
