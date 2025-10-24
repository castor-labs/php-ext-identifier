# Changelog

All notable changes to the PHP Identifier Extension will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2024-10-21

### Added
- Initial release of the PHP Identifier Extension
- Complete UUID support for all versions (1, 3, 4, 5, 6, 7)
- ULID (Universally Unique Lexicographically Sortable Identifier) support
- Bit128 base class for 128-bit identifier operations
- Context system for deterministic testing and generation
- High-performance native C implementation
- Comprehensive test suite with 23+ PHPT tests
- Full RFC 4122 and RFC 9562 compliance
- Complete arginfo declarations for all methods
- Crockford Base32 encoding/decoding support
- System and Fixed context implementations
- Memory-efficient binary operations
- Type-safe PHP class hierarchy

### Features
- **UUID Generation**: All standard UUID versions with proper formatting
- **ULID Generation**: Monotonic, sortable identifiers with timestamp
- **Context System**: Deterministic generation for testing scenarios
- **Performance**: Native C implementation for optimal speed
- **Type Safety**: Complete PHP type declarations and validation
- **Standards Compliance**: Full RFC compliance for UUID and ULID formats
- **Testing**: Comprehensive test coverage with deterministic contexts

### Technical Details
- Minimum PHP version: 8.1.0
- Zero external dependencies
- Memory-efficient 128-bit operations
- Thread-safe implementation
- Complete error handling and validation
- Full IDE support with type hints and autocompletion

### Documentation
- Complete API documentation
- Installation and usage guides
- Contributing guidelines
- Performance benchmarks
- RFC compliance details
