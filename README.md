# Extension Identifier

A high-performance PHP extension for working with 128-bit identifiers including UUIDs and ULIDs.

## Features

- **128-bit Base Class**: `Identifier\Bit128` for all 128-bit identifiers
- **Complete UUID Support**: All UUID versions (1, 3, 4, 5, 6, 7) with proper RFC compliance
- **ULID Support**: Universally Unique Lexicographically Sortable Identifiers with monotonic ordering
- **Thread Safety**: Full thread safety for ULID monotonic generation using TSRM (Thread Safe Resource Manager)
- **State System**: Deterministic generation for testing with `State\Fixed`
- **Exceptional Performance**: Native C implementation delivering 9.9M+ ULID ops/sec, 2.8M+ UUID ops/sec
- **Type Safety**: Proper PHP class hierarchy with inheritance

## Installation

### Requirements

- PHP 8.1 or higher
- Zig 0.15.2+ ([Download from ziglang.org](https://ziglang.org/download/)) - *The amazing build system that makes this all possible*

If you use [devenv](https://devenv.sh/), the repository includes a ready-to-use environment with PHP, PHP development headers (`php-config`), Composer, Zig, and ZLS:

```bash
devenv shell
```

Inside the devenv shell, you can use the provided helper scripts:

```bash
build # zig build
test  # zig build + PHPUnit
dev   # zig build dev
bench # zig build + composer install + composer bench
```

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

## Testing with Fixed State

```php
use Identifier\State\Fixed;
use Identifier\Uuid\Version4;

// Create deterministic state for testing
$ctx = Fixed::create(1640995200000, 12345);

// Generate deterministic UUIDs
$uuid1 = Version4::generate($ctx);
$uuid7 = Version7::generate($ctx);
$ulid = Ulid::generate($ctx);

// Both calls with same state will produce same results
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

Tests are written with PHPUnit under `test/Test` and are run against the locally built extension.

```bash
zig build test      # Build the extension and run PHPUnit
zig build dev       # Build + test
composer test       # Run PHPUnit after the extension has been built
```

With devenv:

```bash
devenv shell test   # Build and run PHPUnit inside the managed environment
devenv test         # Validate the environment and run the test suite
```

## Benchmarks

Benchmarks use [PHPBench](https://phpbench.readthedocs.io/) and compare this extension against [symfony/uid](https://symfony.com/doc/current/components/uid.html) and [ramsey/uuid](https://uuid.ramsey.dev/).

```bash
zig build
composer install
composer bench
```

With devenv:

```bash
devenv shell bench
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
- **[devenv](https://devenv.sh/)** - Provides a reproducible development environment for PHP extension work, including PHP development tooling, Composer, Zig, and ZLS.
- [RFC 4122](https://tools.ietf.org/html/rfc4122) - UUID specification
- [RFC 9562](https://tools.ietf.org/html/rfc9562) - Updated UUID specification
- [ULID Specification](https://github.com/ulid/spec) - ULID format specification
