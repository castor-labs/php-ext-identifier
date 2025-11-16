# Extension Identifier

A high-performance PHP extension for working with 128-bit identifiers including UUIDs and ULIDs.

## Features

- **128-bit Base Class**: `Identifier\Bit128` for all 128-bit identifiers
- **Complete UUID Support**: All UUID versions (1, 3, 4, 5, 6, 7) with proper RFC compliance
- **ULID Support**: Universally Unique Lexicographically Sortable Identifiers with monotonic ordering
- **Thread Safety**: Full thread safety for ULID monotonic generation using TSRM (Thread Safe Resource Manager)
- **Context System**: Deterministic generation for testing with `FixedContext`
- **Exceptional Performance**: Native C implementation delivering 9.9M+ ULID ops/sec, 2.8M+ UUID ops/sec
- **Type Safety**: Proper PHP class hierarchy with inheritance

## Installation

### Requirements

- PHP 8.1 or higher
- Zig 0.15.2+ ([Download from ziglang.org](https://ziglang.org/download/)) - *The amazing build system that makes this all possible*

### Build and Install

```bash
git clone https://github.com/your-org/php-ext-identifier.git
cd php-ext-identifier
zig build dev  # Build + test in one command
```

For production installation:
```bash
zig build install-system  # Install to system PHP (requires sudo)
```

### Enable Extension

Add to your `php.ini`:
```ini
extension=identifier
```

## Quick Start

```php
use Identifier\Uuid\Version4;
use Identifier\Uuid\Version7;
use Identifier\Ulid;

// Generate random UUID v4
$uuid = Version4::generate();
echo $uuid->toString(); // e.g., "550e8400-e29b-41d4-a716-446655440000"

// Generate timestamp-based UUID v7
$uuid7 = Version7::generate();
echo $uuid7->toString();

// Generate ULID
$ulid = Ulid::generate();
echo $ulid->toString(); // e.g., "01ARZ3NDEKTSV4RRFFQ69G5FAV"
```

## Testing with Fixed Context

```php
use Identifier\Context\Fixed;
use Identifier\Uuid\Version4;

// Create deterministic context for testing
$ctx = Fixed::create(1640995200000, 12345);

// Generate deterministic UUIDs
$uuid1 = Version4::generate($ctx);
$uuid7 = Version7::generate($ctx);
$ulid = Ulid::generate($ctx);

// Both calls with same context will produce same results
```

## Thread Safety

This extension is **fully thread-safe** for ULID monotonic generation in multi-threaded PHP environments (ZTS builds). The implementation uses PHP's TSRM (Thread Safe Resource Manager) to ensure proper thread isolation.

### How Thread Safety Works

- **Thread Isolation**: Each thread maintains its own monotonic state (last timestamp and randomness)
- **Zero Contention**: No locks or synchronization required - each thread operates independently
- **Monotonic Ordering**: ULIDs generated within a single thread are guaranteed to be monotonically increasing
- **Performance**: Thread safety comes with zero performance overhead

### Compatibility

- ✅ **Apache mod_php** (both threaded and non-threaded)
- ✅ **Apache mod_worker** (requires ZTS PHP build)
- ✅ **Windows IIS** (requires ZTS PHP build)
- ✅ **PHP-FPM** (process-based, inherently thread-safe)
- ✅ **CLI** (single-threaded by default)

### Build Considerations

The same extension binary works for both ZTS (thread-safe) and non-ZTS PHP builds:

```bash
# Check if your PHP build supports threading
php -m | grep -i zts
php-config --configure-options | grep -i zts
```

For web servers that use threading (like Apache mod_worker or IIS), ensure you're using a ZTS PHP build to get full thread safety benefits.

## API Documentation

Check the [stub file](stubs/identifier.stub.php) for a detailed API documentation.

## Testing

```bash
zig build test      # Run all tests
zig build dev       # Build + test
```

## Contributing

1. Fork the repository
2. Create a feature branch
3. Add tests for new functionality
4. Ensure all tests pass (`zig build test`)
5. Submit a pull request

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Acknowledgments

- **[Zig Programming Language](https://ziglang.org/)** - This project is built using Zig's incredible embedded C compiler and build system. Zig's seamless C interop, cross-compilation capabilities, and modern build tooling make it the perfect choice for PHP extension development. Special thanks to the Zig team for creating such an outstanding development experience.
- [RFC 4122](https://tools.ietf.org/html/rfc4122) - UUID specification
- [RFC 9562](https://tools.ietf.org/html/rfc9562) - Updated UUID specification
- [ULID Specification](https://github.com/ulid/spec) - ULID format specification
