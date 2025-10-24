# Contributing to PHP Identifier Extension

Thank you for your interest in contributing to the PHP Identifier Extension! This document provides guidelines and information for contributors.

## Development Setup

### Prerequisites

- PHP 8.1 or higher with development headers
- GCC or compatible C compiler
- Make
- Git

### Setting Up the Development Environment

1. Clone the repository:
```bash
git clone https://github.com/your-org/php-ext-identifier.git
cd php-ext-identifier
```

2. Set up the development environment:
```bash
make dev-setup
```

3. Build the extension:
```bash
make build
```

4. Run tests:
```bash
make test
```

## Code Style

- Follow PHP extension coding standards
- Use consistent indentation (4 spaces for C code)
- Add appropriate comments for complex logic
- Format code with clang-format if available: `make format`

## Testing

- All new features must include tests
- Tests should be written in PHP Test (PHPT) format
- Place test files in the `tests/` directory
- Run tests with `make test`
- Check for memory leaks with `make valgrind` (if valgrind is available)

## Submitting Changes

1. Fork the repository
2. Create a feature branch: `git checkout -b feature/your-feature-name`
3. Make your changes
4. Add tests for new functionality
5. Ensure all tests pass: `make test`
6. Commit your changes with descriptive messages
7. Push to your fork: `git push origin feature/your-feature-name`
8. Submit a pull request

## Pull Request Guidelines

- Provide a clear description of the changes
- Reference any related issues
- Ensure all tests pass
- Include documentation updates if needed
- Keep commits focused and atomic

## Reporting Issues

When reporting issues, please include:

- PHP version and extension version
- Operating system and compiler information
- Minimal code example that reproduces the issue
- Expected vs actual behavior
- Any error messages or stack traces

## Development Guidelines

### Adding New Features

1. Update the stub file (`stubs/identifier.stub.php`) first
2. Implement the C code
3. Add comprehensive tests
4. Update documentation

### Code Organization

- **Header files**: `src/php_identifier.h` - Main header with declarations
- **Core files**: `src/php_identifier.c` - Extension initialization
- **Class implementations**: `src/*.c` - Individual class implementations
- **Tests**: `tests/*.phpt` - PHP test files
- **Documentation**: `docs/` - API documentation

### Memory Management

- Always use PHP's memory management functions (`emalloc`, `efree`, etc.)
- Check for memory leaks with valgrind
- Properly handle reference counting for zval objects
- Clean up resources in object destructors

### Error Handling

- Use appropriate PHP exception classes
- Provide meaningful error messages
- Validate input parameters thoroughly
- Handle edge cases gracefully

## License

By contributing to this project, you agree that your contributions will be licensed under the MIT License.

## Questions?

If you have questions about contributing, please:

1. Check existing issues and documentation
2. Open a new issue for discussion
3. Join our community discussions

Thank you for contributing!
