<?php

/**
 * Stubs for identifier extension
 * 
 * Generated from extension reflection with full C source documentation.
 * 
 * @version 1.0.0
 * @generated 2025-10-24 23:12:33
 */

namespace Php\Identifier
{
    class Context
    {
    }

    class Bit128
    {
        /**
         * Create a new 128-bit identifier from bytes
         * Constructs a new Bit128 instance from exactly 16 bytes of binary data.
         * This is the base class for all 128-bit identifiers in this extension.
         * 
         * @param string $bytes Exactly 16 bytes of binary data
         * @throws Exception If bytes is not exactly 16 bytes long
         * 
         * @example
         * ```php
         * $bytes = random_bytes(16);
         * $bit128 = new Bit128($bytes);
         * ```
         * @since 1.0.0
         */
        public function __construct(string $bytes) {}

        /**
         * Get the raw 16-byte binary representation
         * Returns the internal 16-byte binary data that represents this 128-bit identifier.
         * This is the most compact representation and is useful for storage and transmission.
         * 
         * @return string The 16-byte binary representation
         * 
         * @example
         * ```php
         * $id = new Bit128(random_bytes(16));
         * $bytes = $id->getBytes();
         * echo strlen($bytes); // 16
         * echo bin2hex($bytes); // hex representation
         * ```
         * @since 1.0.0
         */
        public function getBytes(): string {}

        /**
         * Alias for getBytes() method
         * This method is an alias for getBytes() and returns the same 16-byte binary data.
         * Provided for API consistency and convenience.
         * 
         * @return string The 16-byte binary representation
         * 
         * @example
         * ```php
         * $id = new Bit128(random_bytes(16));
         * $bytes1 = $id->getBytes();
         * $bytes2 = $id->toBytes();
         * var_dump($bytes1 === $bytes2); // bool(true)
         * ```
         * @since 1.0.0
         */
        public function toBytes(): string {}

        /**
         * Check if this identifier equals another
         * Performs a byte-by-byte comparison to determine if two 128-bit
         * identifiers contain exactly the same data.
         * 
         * @param Bit128 $other The identifier to compare against
         * @return bool True if the identifiers are equal, false otherwise
         * 
         * @example
         * ```php
         * $id1 = Bit128::fromHex('550e8400e29b41d4a716446655440000');
         * $id2 = Bit128::fromHex('550e8400e29b41d4a716446655440000');
         * var_dump($id1->equals($id2)); // bool(true)
         * ```
         * @since 1.0.0
         */
        public function equals(\Php\Identifier\Bit128 $other): bool {}

        /**
         * Compare this identifier with another for ordering
         * Performs a lexicographic comparison of the binary data to determine
         * the relative ordering of two 128-bit identifiers. Useful for sorting.
         * 
         * @param Bit128 $other The identifier to compare against
         * @return int -1 if this < other, 0 if equal, 1 if this > other
         * 
         * @example
         * ```php
         * $id1 = Bit128::fromHex('550e8400e29b41d4a716446655440000');
         * $id2 = Bit128::fromHex('550e8400e29b41d4a716446655440001');
         * echo $id1->compare($id2); // -1 (id1 < id2)
         * echo $id2->compare($id1); // 1 (id2 > id1)
         * echo $id1->compare($id1); // 0 (equal)
         * ```
         * @since 1.0.0
         */
        public function compare(\Php\Identifier\Bit128 $other): int {}

        /**
         * Convert the identifier to a hexadecimal string
         * Returns a 32-character lowercase hexadecimal representation of the
         * 128-bit identifier. This is useful for debugging and storage.
         * 
         * @return string 32-character hexadecimal string (lowercase)
         * 
         * @example
         * ```php
         * $id = Bit128::fromHex('550e8400e29b41d4a716446655440000');
         * echo $id->toHex(); // "550e8400e29b41d4a716446655440000"
         * ```
         * @since 1.0.0
         */
        public function toHex(): string {}

        /**
         * Create a new identifier from a hexadecimal string
         * Parses a 32-character hexadecimal string (with or without dashes)
         * and creates a new Bit128 instance. The hex string is case-insensitive.
         * 
         * @param string $hex 32-character hexadecimal string (case-insensitive)
         * @return Bit128 New identifier instance
         * @throws Exception If hex string is invalid or wrong length
         * 
         * @example
         * ```php
         * $id = Bit128::fromHex('550e8400e29b41d4a716446655440000');
         * $same = Bit128::fromHex('550E8400-E29B-41D4-A716-446655440000');
         * var_dump($id->equals($same)); // bool(true)
         * ```
         * @since 1.0.0
         */
        public static function fromHex(string $hex): \Php\Identifier\Bit128 {}

        /**
         * Create a new identifier from binary data
         * Creates a new Bit128 instance from exactly 16 bytes of binary data.
         * This is useful when reading identifiers from binary storage or network protocols.
         * 
         * @param string $bytes Exactly 16 bytes of binary data
         * @return Bit128 New identifier instance
         * @throws Exception If bytes is not exactly 16 bytes long
         * 
         * @example
         * ```php
         * $bytes = random_bytes(16);
         * $id = Bit128::fromBytes($bytes);
         * var_dump($id->getBytes() === $bytes); // bool(true)
         * // From hex string converted to bytes
         * $hex = '550e8400e29b41d4a716446655440000';
         * $bytes = hex2bin($hex);
         * $id = Bit128::fromBytes($bytes);
         * echo $id->toHex(); // "550e8400e29b41d4a716446655440000"
         * ```
         * @since 1.0.0
         */
        public static function fromBytes(string $bytes): \Php\Identifier\Bit128 {}

    }

    class Uuid extends \Php\Identifier\Bit128 implements \Stringable
    {
        /**
         * Get the UUID version number
         * Returns the version number stored in bits 12-15 of the time_hi_and_version field.
         * This indicates which UUID generation algorithm was used.
         * 
         * @return int The UUID version (1, 3, 4, 5, 6, or 7)
         * 
         * @example
         * ```php
         * $uuid = Version4::generate();
         * echo $uuid->getVersion(); // 4
         * $uuid = Version1::generate();
         * echo $uuid->getVersion(); // 1
         * ```
         * @since 1.0.0
         */
        public function getVersion(): int {}

        /**
         * Get the UUID variant
         * Returns the variant field which indicates the layout of the UUID.
         * For RFC 4122 UUIDs, this should always be 2 (binary 10).
         * 
         * @return int The UUID variant (typically 2 for RFC 4122)
         * 
         * @example
         * ```php
         * $uuid = Version4::generate();
         * echo $uuid->getVariant(); // 2 (RFC 4122 variant)
         * ```
         * @since 1.0.0
         */
        public function getVariant(): int {}

        /**
         * Convert UUID to standard string representation
         * Returns the UUID in the standard 8-4-4-4-12 hexadecimal format with hyphens.
         * This is the canonical string representation defined by RFC 4122.
         * 
         * @return string UUID in format "xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx"
         * 
         * @example
         * ```php
         * $uuid = Version4::generate();
         * echo $uuid->toString(); // "f47ac10b-58cc-4372-a567-0e02b2c3d479"
         * // Can also use string casting
         * echo (string) $uuid; // Same result
         * ```
         * @since 1.0.0
         */
        public function toString(): string {}

        /**
         * Magic method for string conversion
         * Allows the UUID to be automatically converted to a string when used in
         * string contexts. Delegates to the toString() method.
         * 
         * @return string UUID in standard format
         * 
         * @example
         * ```php
         * $uuid = Version4::generate();
         * echo $uuid; // Automatically calls __toString()
         * echo "UUID: $uuid"; // String interpolation
         * ```
         * @since 1.0.0
         */
        public function __toString(): string {}

        public static function fromString(string $uuid): \Php\Identifier\Uuid {}

        public static function fromBytes(string $bytes): \Php\Identifier\Uuid {}

        public static function fromHex(string $hex): \Php\Identifier\Uuid {}

        public function isNil(): bool {}

        public static function nil(): \Php\Identifier\Uuid {}

        public function isMax(): bool {}

        public static function max(): \Php\Identifier\Uuid {}

    }

    final class Ulid extends \Php\Identifier\Bit128 implements \Stringable
    {
        /**
         * Generate a new ULID (Universally Unique Lexicographically Sortable Identifier)
         * Creates a new ULID with a timestamp component and random component.
         * ULIDs are lexicographically sortable and encode a timestamp, making them
         * ideal for use as database primary keys and distributed system identifiers.
         * 
         * @param Context|null $context Optional context for controlling time and randomness
         * @return Ulid A new ULID instance
         * @throws Exception If timestamp or random generation fails
         * 
         * @example
         * ```php
         * // Generate with current timestamp
         * $ulid = Ulid::generate();
         * echo $ulid->toString(); // e.g., "01ARZ3NDEKTSV4RRFFQ69G5FAV"
         * // Generate with fixed context for testing
         * $context = new FixedContext();
         * $ulid = Ulid::generate($context);
         * ```
         * @since 1.0.0
         */
        public static function generate(?\Php\Identifier\Context $context = NULL): \Php\Identifier\Ulid {}

        /**
         * Convert ULID to string representation
         * Returns the ULID in its canonical 26-character Crockford Base32 encoding.
         * This encoding is case-insensitive and excludes ambiguous characters.
         * 
         * @return string 26-character ULID string
         * 
         * @example
         * ```php
         * $ulid = Ulid::generate();
         * echo $ulid->toString(); // "01ARZ3NDEKTSV4RRFFQ69G5FAV"
         * // Can also use string casting
         * echo (string) $ulid; // Same result
         * ```
         * @since 1.0.0
         */
        public function toString(): string {}

        public function __toString(): string {}

        public static function fromString(string $ulid): \Php\Identifier\Ulid {}

        public static function fromHex(string $hex): \Php\Identifier\Ulid {}

        public static function fromBytes(string $bytes): \Php\Identifier\Ulid {}

        /**
         * Get the timestamp component of the ULID
         * Extracts and returns the 48-bit timestamp from the first 6 bytes of the ULID.
         * This represents milliseconds since Unix epoch (January 1, 1970).
         * 
         * @return int Timestamp in milliseconds since Unix epoch
         * 
         * @example
         * ```php
         * $ulid = Ulid::generate();
         * $timestamp = $ulid->getTimestamp();
         * echo date('Y-m-d H:i:s', $timestamp / 1000); // Convert to readable date
         * // ULIDs are sortable by timestamp
         * $ulid1 = Ulid::generate();
         * usleep(1000); // Wait 1ms
         * $ulid2 = Ulid::generate();
         * var_dump($ulid1->getTimestamp() < $ulid2->getTimestamp()); // bool(true)
         * ```
         * @since 1.0.0
         */
        public function getTimestamp(): int {}

        /**
         * Get the randomness component of the ULID
         * Extracts and returns the 80-bit randomness from the last 10 bytes of the ULID.
         * This provides uniqueness when multiple ULIDs are generated in the same millisecond.
         * 
         * @return string 10-byte binary randomness data
         * 
         * @example
         * ```php
         * $ulid = Ulid::generate();
         * $randomness = $ulid->getRandomness();
         * echo strlen($randomness); // 10
         * echo bin2hex($randomness); // 20-character hex string
         * // Different ULIDs have different randomness
         * $ulid1 = Ulid::generate();
         * $ulid2 = Ulid::generate();
         * var_dump($ulid1->getRandomness() !== $ulid2->getRandomness()); // bool(true)
         * ```
         * @since 1.0.0
         */
        public function getRandomness(): string {}

    }

}

namespace Php\Identifier\Context
{
    class System implements \Php\Identifier\Context
    {
        /**
         * Get the singleton system context instance
         * Returns the shared system context that uses real system time and
         * cryptographically secure random number generation. This is the
         * default context used when no context is specified.
         * 
         * @return System The singleton system context instance
         * 
         * @example
         * ```php
         * $context = System::getInstance();
         * $uuid = Version4::generate($context);
         * $ulid = Ulid::generate($context);
         * // Same instance every time
         * $context2 = System::getInstance();
         * var_dump($context === $context2); // bool(true)
         * ```
         * @since 1.0.0
         */
        public static function getInstance(): \Php\Identifier\Context\System {}

        public function getTimestampMs(): int {}

        public function getGregorianEpochTime(): int {}

        public function getRandomBytes(int $length): string {}

    }

    class Fixed implements \Php\Identifier\Context
    {
        /**
         * Create a new fixed context for testing
         * Creates a context with fixed timestamp and deterministic random bytes.
         * This is primarily useful for testing and generating reproducible identifiers.
         * 
         * @param int $timestamp Fixed timestamp in milliseconds since Unix epoch
         * @param string $randomBytes Fixed random bytes (16 bytes for deterministic generation)
         * @return Fixed A new fixed context instance
         * @throws Exception If randomBytes is not exactly 16 bytes
         * 
         * @example
         * ```php
         * // Create fixed context for testing
         * $timestamp = 1640995200000; // 2022-01-01 00:00:00 UTC
         * $randomBytes = str_repeat("\x00", 16); // All zeros
         * $context = Fixed::create($timestamp, $randomBytes);
         * // Generate reproducible identifiers
         * $uuid1 = Version4::generate($context);
         * $uuid2 = Version4::generate($context);
         * var_dump($uuid1->equals($uuid2)); // bool(true) - same every time
         * ```
         * @since 1.0.0
         */
        public static function create(int $timestamp_ms, int $seed): \Php\Identifier\Context\Fixed {}

        public function advanceTime(int $milliseconds): void {}

        public function advanceTimeSeconds(int $seconds): void {}

        public function setTimestamp(int $timestamp_ms): void {}

        public function getTimestampMs(): int {}

        public function getGregorianEpochTime(): int {}

        public function getRandomBytes(int $length): string {}

    }

}

namespace Php\Identifier\Uuid
{
    final class Version1 extends \Php\Identifier\Uuid implements \Stringable
    {
        /**
         * Generate a new UUID version 1 (time-based)
         * Creates a UUID version 1 based on the current timestamp, clock sequence,
         * and node ID (MAC address). This provides temporal uniqueness and allows
         * for sorting by creation time.
         * 
         * @param Context|null $context Optional context for controlling time and node
         * @return Version1 A new UUID version 1 instance
         * @throws Exception If timestamp or node generation fails
         * 
         * @example
         * ```php
         * // Generate with system time and MAC address
         * $uuid = Version1::generate();
         * echo $uuid->toString(); // "6ba7b810-9dad-11d1-80b4-00c04fd430c8"
         * // Generate with fixed context for testing
         * $context = new FixedContext();
         * $uuid = Version1::generate($context);
         * ```
         * @since 1.0.0
         */
        public static function generate(?\Php\Identifier\Context $context = NULL): \Php\Identifier\Uuid\Version1 {}

        public static function fromString(string $uuid): \Php\Identifier\Uuid\Version1 {}

        public static function fromBytes(string $bytes): \Php\Identifier\Uuid\Version1 {}

        public static function fromHex(string $hex): \Php\Identifier\Uuid\Version1 {}

        public function getTimestamp(): int {}

        public function getNode(): string {}

        public function getClockSequence(): int {}

    }

    final class Version3 extends \Php\Identifier\Uuid implements \Stringable
    {
        /**
         * Generate a new UUID version 3 (name-based with MD5)
         * Creates a UUID version 3 by hashing a namespace UUID and name using MD5.
         * The same namespace and name will always produce the same UUID, making
         * this suitable for deterministic identifier generation.
         * 
         * @param string $namespace UUID namespace as string
         * @param string $name Name to hash within the namespace
         * @return Version3 A new UUID version 3 instance
         * @throws Exception If namespace is invalid or hashing fails
         * 
         * @example
         * ```php
         * // Generate deterministic UUID from namespace and name
         * $namespace = "6ba7b810-9dad-11d1-80b4-00c04fd430c8";
         * $uuid = Version3::generate($namespace, "example.com");
         * echo $uuid->toString(); // Always the same for these inputs
         * // Same inputs always produce same UUID
         * $uuid2 = Version3::generate($namespace, "example.com");
         * var_dump($uuid->equals($uuid2)); // bool(true)
         * ```
         * @since 1.0.0
         */
        public static function generate(string $namespace, string $name): \Php\Identifier\Uuid\Version3 {}

        public static function fromString(string $uuid): \Php\Identifier\Uuid\Version3 {}

        public static function fromBytes(string $bytes): \Php\Identifier\Uuid\Version3 {}

        public static function fromHex(string $hex): \Php\Identifier\Uuid\Version3 {}

    }

    final class Version4 extends \Php\Identifier\Uuid implements \Stringable
    {
        /**
         * Generate a new random UUID version 4
         * Creates a new UUID version 4 using cryptographically secure random bytes.
         * Version 4 UUIDs are completely random except for the version and variant bits.
         * 
         * @param Context|null $context Optional context for controlling randomness
         * @return Version4 A new UUID version 4 instance
         * @throws Exception If random byte generation fails
         * 
         * @example
         * ```php
         * // Generate with system randomness
         * $uuid = Version4::generate();
         * echo $uuid->toString(); // e.g., "f47ac10b-58cc-4372-a567-0e02b2c3d479"
         * // Generate with custom context
         * $context = new FixedContext();
         * $uuid = Version4::generate($context);
         * ```
         * @since 1.0.0
         */
        public static function generate(?\Php\Identifier\Context $context = NULL): \Php\Identifier\Uuid\Version4 {}

        public static function fromString(string $uuid): \Php\Identifier\Uuid\Version4 {}

        public static function fromBytes(string $bytes): \Php\Identifier\Uuid\Version4 {}

        public static function fromHex(string $hex): \Php\Identifier\Uuid\Version4 {}

        /**
         * Get the random bytes from this UUID
         * Returns all 16 bytes of the UUID, including the version and variant bits.
         * This is the raw binary representation of the UUID.
         * 
         * @return string The 16-byte binary representation
         * 
         * @example
         * ```php
         * $uuid = Version4::generate();
         * $bytes = $uuid->getRandomBytes();
         * echo strlen($bytes); // 16
         * ```
         * @since 1.0.0
         */
        public function getRandomBytes(): string {}

        /**
         * Get the pure random bytes without version/variant bits
         * Returns the 16 bytes with the version and variant bits cleared,
         * showing only the random data that was originally generated.
         * 
         * @return string The 16-byte binary data with version/variant bits cleared
         * 
         * @example
         * ```php
         * $uuid = Version4::generate();
         * $pure = $uuid->getPureRandomBytes();
         * $raw = $uuid->getRandomBytes();
         * // $pure has version/variant bits cleared, $raw has them set
         * ```
         * @since 1.0.0
         */
        public function getPureRandomBytes(): string {}

    }

    final class Version5 extends \Php\Identifier\Uuid implements \Stringable
    {
        /**
         * Generate a new UUID version 5 (name-based with SHA-1)
         * Creates a UUID version 5 by hashing a namespace UUID and name using SHA-1.
         * The same namespace and name will always produce the same UUID. This is
         * preferred over version 3 due to SHA-1 being more secure than MD5.
         * 
         * @param string $namespace UUID namespace as string
         * @param string $name Name to hash within the namespace
         * @return Version5 A new UUID version 5 instance
         * @throws Exception If namespace is invalid or hashing fails
         * 
         * @example
         * ```php
         * // Generate deterministic UUID from namespace and name
         * $namespace = "6ba7b810-9dad-11d1-80b4-00c04fd430c8";
         * $uuid = Version5::generate($namespace, "example.com");
         * echo $uuid->toString(); // Always the same for these inputs
         * // Preferred over Version 3 for security
         * $uuid3 = Version3::generate($namespace, "example.com");
         * $uuid5 = Version5::generate($namespace, "example.com");
         * // Different results due to different hash algorithms
         * ```
         * @since 1.0.0
         */
        public static function generate(string $namespace, string $name): \Php\Identifier\Uuid\Version5 {}

        /**
         * Create UUID version 5 from string representation
         * Parses a UUID version 5 from its standard string representation.
         * The string must be in the format "xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx"
         * and must be a valid version 5 UUID.
         * 
         * @param string $uuid UUID string in standard format
         * @return Version5 A new UUID version 5 instance
         * @throws Exception If string is invalid or not version 5
         * 
         * @example
         * ```php
         * $uuid = Version5::fromString("6ba7b810-9dad-11d1-80b4-00c04fd430c8");
         * echo $uuid->getVersion(); // 5
         * ```
         * @since 1.0.0
         */
        public static function fromString(string $uuid): \Php\Identifier\Uuid\Version5 {}

        /**
         * Create UUID version 5 from binary data
         * Creates a UUID version 5 instance from exactly 16 bytes of binary data.
         * The binary data must represent a valid version 5 UUID.
         * 
         * @param string $bytes Exactly 16 bytes of binary data
         * @return Version5 A new UUID version 5 instance
         * @throws Exception If bytes is not exactly 16 bytes or not version 5
         * 
         * @example
         * ```php
         * $bytes = hex2bin("6ba7b8109dad11d180b400c04fd430c8");
         * $uuid = Version5::fromBytes($bytes);
         * echo $uuid->getVersion(); // 5
         * ```
         * @since 1.0.0
         */
        public static function fromBytes(string $bytes): \Php\Identifier\Uuid\Version5 {}

        /**
         * Create UUID version 5 from hexadecimal string
         * Creates a UUID version 5 instance from a 32-character hexadecimal string.
         * The hex string can be with or without hyphens and is case-insensitive.
         * 
         * @param string $hex 32-character hexadecimal string (with or without hyphens)
         * @return Version5 A new UUID version 5 instance
         * @throws Exception If hex string is invalid or not version 5
         * 
         * @example
         * ```php
         * $uuid = Version5::fromHex("6ba7b8109dad11d180b400c04fd430c8");
         * echo $uuid->toString(); // "6ba7b810-9dad-11d1-80b4-00c04fd430c8"
         * ```
         * @since 1.0.0
         */
        public static function fromHex(string $hex): \Php\Identifier\Uuid\Version5 {}

    }

    final class Version6 extends \Php\Identifier\Uuid implements \Stringable
    {
        /**
         * Generate a new UUID version 6 (reordered time-based)
         * Creates a UUID version 6 which is like version 1 but with reordered timestamp
         * fields for better database sorting. The timestamp is in big-endian format
         * making UUIDs naturally sortable by creation time.
         * 
         * @param Context|null $context Optional context for controlling time and node
         * @return Version6 A new UUID version 6 instance
         * @throws Exception If timestamp or node generation fails
         * 
         * @example
         * ```php
         * // Generate with system time and MAC address
         * $uuid = Version6::generate();
         * echo $uuid->toString(); // "1ec9414c-232a-6b00-b3c8-9e6bdeced846"
         * // UUIDs are naturally sortable by timestamp
         * $uuid1 = Version6::generate();
         * usleep(1000);
         * $uuid2 = Version6::generate();
         * var_dump($uuid1->toString() < $uuid2->toString()); // bool(true)
         * ```
         * @since 1.0.0
         */
        public static function generate(?\Php\Identifier\Context $context = NULL): \Php\Identifier\Uuid\Version6 {}

        /**
         * Create UUID version 6 from string representation
         * Parses a UUID version 6 from its standard string representation.
         * The string must be in the format "xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx"
         * and must be a valid version 6 UUID.
         * 
         * @param string $uuid UUID string in standard format
         * @return Version6 A new UUID version 6 instance
         * @throws Exception If string is invalid or not version 6
         * 
         * @example
         * ```php
         * $uuid = Version6::fromString("1ec9414c-232a-6b00-b3c8-9e6bdeced846");
         * echo $uuid->getVersion(); // 6
         * ```
         * @since 1.0.0
         */
        public static function fromString(string $uuid): \Php\Identifier\Uuid\Version6 {}

        /**
         * Create UUID version 6 from binary data
         * Creates a UUID version 6 instance from exactly 16 bytes of binary data.
         * The binary data must represent a valid version 6 UUID.
         * 
         * @param string $bytes Exactly 16 bytes of binary data
         * @return Version6 A new UUID version 6 instance
         * @throws Exception If bytes is not exactly 16 bytes or not version 6
         * 
         * @example
         * ```php
         * $bytes = hex2bin("1ec9414c232a6b00b3c89e6bdeced846");
         * $uuid = Version6::fromBytes($bytes);
         * echo $uuid->getVersion(); // 6
         * ```
         * @since 1.0.0
         */
        public static function fromBytes(string $bytes): \Php\Identifier\Uuid\Version6 {}

        /**
         * Create UUID version 6 from hexadecimal string
         * Creates a UUID version 6 instance from a 32-character hexadecimal string.
         * The hex string can be with or without hyphens and is case-insensitive.
         * 
         * @param string $hex 32-character hexadecimal string (with or without hyphens)
         * @return Version6 A new UUID version 6 instance
         * @throws Exception If hex string is invalid or not version 6
         * 
         * @example
         * ```php
         * $uuid = Version6::fromHex("1ec9414c232a6b00b3c89e6bdeced846");
         * echo $uuid->toString(); // "1ec9414c-232a-6b00-b3c8-9e6bdeced846"
         * ```
         * @since 1.0.0
         */
        public static function fromHex(string $hex): \Php\Identifier\Uuid\Version6 {}

        /**
         * Get the timestamp from UUID version 6
         * Extracts the timestamp component from the UUID version 6. The timestamp
         * represents the number of 100-nanosecond intervals since October 15, 1582.
         * 
         * @return int Timestamp in 100-nanosecond intervals since UUID epoch
         * 
         * @example
         * ```php
         * $uuid = Version6::generate();
         * $timestamp = $uuid->getTimestamp();
         * // Convert to Unix timestamp (seconds)
         * $unixTime = ($timestamp - 0x01b21dd213814000) / 10000000;
         * echo date('Y-m-d H:i:s', $unixTime);
         * ```
         * @since 1.0.0
         */
        public function getTimestamp(): int {}

        /**
         * Get the node identifier from UUID version 6
         * Extracts the 6-byte node identifier, typically the MAC address of the
         * network interface. If no MAC address is available, a random node ID is used.
         * 
         * @return string 6-byte node identifier
         * 
         * @example
         * ```php
         * $uuid = Version6::generate();
         * $node = $uuid->getNode();
         * echo bin2hex($node); // e.g., "9e6bdeced846"
         * echo strlen($node); // 6
         * ```
         * @since 1.0.0
         */
        public function getNode(): string {}

        /**
         * Get the clock sequence from UUID version 6
         * Extracts the 14-bit clock sequence used to help avoid duplicates when
         * the clock is set backwards or the node ID changes.
         * 
         * @return int Clock sequence value (0-16383)
         * 
         * @example
         * ```php
         * $uuid = Version6::generate();
         * $clockSeq = $uuid->getClockSequence();
         * echo $clockSeq; // e.g., 12345
         * ```
         * @since 1.0.0
         */
        public function getClockSequence(): int {}

    }

    final class Version7 extends \Php\Identifier\Uuid implements \Stringable
    {
        /**
         * Generate a new UUID version 7 (Unix timestamp-based)
         * Creates a UUID version 7 with a Unix timestamp in milliseconds followed by
         * random data. This provides natural sorting by creation time and is the
         * recommended UUID version for new applications.
         * 
         * @param Context|null $context Optional context for controlling time and randomness
         * @return Version7 A new UUID version 7 instance
         * @throws Exception If timestamp or random generation fails
         * 
         * @example
         * ```php
         * // Generate with current timestamp
         * $uuid = Version7::generate();
         * echo $uuid->toString(); // "018c2e65-4b0a-7c3d-8f2e-1a4b5c6d7e8f"
         * // UUIDs are naturally sortable by timestamp
         * $uuid1 = Version7::generate();
         * usleep(1000);
         * $uuid2 = Version7::generate();
         * var_dump($uuid1->toString() < $uuid2->toString()); // bool(true)
         * ```
         * @since 1.0.0
         */
        public static function generate(?\Php\Identifier\Context $context = NULL): \Php\Identifier\Uuid\Version7 {}

        /**
         * Create UUID version 7 from string representation
         * Parses a UUID version 7 from its standard string representation.
         * The string must be in the format "xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx"
         * and must be a valid version 7 UUID.
         * 
         * @param string $uuid UUID string in standard format
         * @return Version7 A new UUID version 7 instance
         * @throws Exception If string is invalid or not version 7
         * 
         * @example
         * ```php
         * $uuid = Version7::fromString("018c2e65-4b0a-7c3d-8f2e-1a4b5c6d7e8f");
         * echo $uuid->getVersion(); // 7
         * ```
         * @since 1.0.0
         */
        public static function fromString(string $uuid): \Php\Identifier\Uuid\Version7 {}

        /**
         * Create UUID version 7 from binary data
         * Creates a UUID version 7 instance from exactly 16 bytes of binary data.
         * The binary data must represent a valid version 7 UUID.
         * 
         * @param string $bytes Exactly 16 bytes of binary data
         * @return Version7 A new UUID version 7 instance
         * @throws Exception If bytes is not exactly 16 bytes or not version 7
         * 
         * @example
         * ```php
         * $bytes = hex2bin("018c2e654b0a7c3d8f2e1a4b5c6d7e8f");
         * $uuid = Version7::fromBytes($bytes);
         * echo $uuid->getVersion(); // 7
         * ```
         * @since 1.0.0
         */
        public static function fromBytes(string $bytes): \Php\Identifier\Uuid\Version7 {}

        /**
         * Create UUID version 7 from hexadecimal string
         * Creates a UUID version 7 instance from a 32-character hexadecimal string.
         * The hex string can be with or without hyphens and is case-insensitive.
         * 
         * @param string $hex 32-character hexadecimal string (with or without hyphens)
         * @return Version7 A new UUID version 7 instance
         * @throws Exception If hex string is invalid or not version 7
         * 
         * @example
         * ```php
         * $uuid = Version7::fromHex("018c2e654b0a7c3d8f2e1a4b5c6d7e8f");
         * echo $uuid->toString(); // "018c2e65-4b0a-7c3d-8f2e-1a4b5c6d7e8f"
         * ```
         * @since 1.0.0
         */
        public static function fromHex(string $hex): \Php\Identifier\Uuid\Version7 {}

        /**
         * Get the timestamp from UUID version 7
         * Extracts the 48-bit Unix timestamp in milliseconds from the UUID version 7.
         * This represents the time when the UUID was generated.
         * 
         * @return int Unix timestamp in milliseconds
         * 
         * @example
         * ```php
         * $uuid = Version7::generate();
         * $timestamp = $uuid->getTimestamp();
         * echo date('Y-m-d H:i:s', $timestamp / 1000); // Convert to readable date
         * // Compare timestamps
         * $uuid1 = Version7::generate();
         * usleep(1000);
         * $uuid2 = Version7::generate();
         * var_dump($uuid1->getTimestamp() < $uuid2->getTimestamp()); // bool(true)
         * ```
         * @since 1.0.0
         */
        public function getTimestamp(): int {}

        /**
         * Get all random bytes from UUID version 7
         * Extracts the 74 bits of random data from the UUID version 7 as 10 bytes.
         * This includes both the rand_a and rand_b fields.
         * 
         * @return string 10 bytes of random data
         * 
         * @example
         * ```php
         * $uuid = Version7::generate();
         * $randomBytes = $uuid->getRandomBytes();
         * echo strlen($randomBytes); // 10
         * echo bin2hex($randomBytes); // e.g., "3d8f2e1a4b5c6d7e8f90"
         * ```
         * @since 1.0.0
         */
        public function getRandomBytes(): string {}

        /**
         * Get the rand_a field from UUID version 7
         * Extracts the 12-bit rand_a field from the UUID version 7. This is the
         * first random component after the timestamp.
         * 
         * @return int 12-bit random value (0-4095)
         * 
         * @example
         * ```php
         * $uuid = Version7::generate();
         * $randA = $uuid->getRandomA();
         * echo $randA; // e.g., 3245 (0-4095)
         * ```
         * @since 1.0.0
         */
        public function getRandomA(): string {}

        /**
         * Get the rand_b field from UUID version 7
         * Extracts the 62-bit rand_b field from the UUID version 7 as 8 bytes.
         * This is the main random component providing uniqueness.
         * 
         * @return string 8 bytes of random data (rand_b field)
         * 
         * @example
         * ```php
         * $uuid = Version7::generate();
         * $randB = $uuid->getRandomB();
         * echo strlen($randB); // 8
         * echo bin2hex($randB); // e.g., "8f2e1a4b5c6d7e8f"
         * ```
         * @since 1.0.0
         */
        public function getRandomB(): string {}

    }

}

namespace Php\Encoding
{
    class Codec
    {
        public const BASE32_RFC4648 = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        public const BASE32_CROCKFORD = '0123456789ABCDEFGHJKMNPQRSTVWXYZ';
        public const BASE58_BITCOIN = '123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz';
        public const BASE64_STANDARD = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/';
        public const BASE64_URLSAFE = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_';
        public const BASE64_MIME = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/';

        /**
         * Create a new encoding codec with custom alphabet
         * Creates a codec that can encode and decode binary data using a custom alphabet.
         * The alphabet defines the characters used for encoding, and an optional padding
         * character can be specified for alignment.
         * 
         * @param string $alphabet The character set to use for encoding (must not be empty)
         * @param string|null $padding Optional padding character (defaults to '=')
         * @throws Exception If alphabet is empty
         * 
         * @example
         * ```php
         * // Create a custom Base32 codec
         * $codec = new Codec('ABCDEFGHIJKLMNOPQRSTUVWXYZ234567');
         * $encoded = $codec->encode('Hello World');
         * // Create codec with custom padding
         * $codec = new Codec('0123456789ABCDEF', '*');
         * $encoded = $codec->encode(random_bytes(16));
         * ```
         * @since 1.0.0
         */
        public function __construct(string $alphabet, ?string $padding = NULL) {}

        /**
         * Encode binary data using the codec's alphabet
         * Converts binary data into a string representation using the codec's
         * character alphabet. The encoding is reversible using the decode() method.
         * 
         * @param string $data Binary data to encode
         * @return string Encoded string using the codec's alphabet
         * @throws Exception If encoding fails
         * 
         * @example
         * ```php
         * $codec = Codec::base32Crockford();
         * $data = random_bytes(16);
         * $encoded = $codec->encode($data);
         * echo $encoded; // e.g., "91JPRV3F5GG7EVVG91IMKM"
         * // Verify round-trip encoding
         * $decoded = $codec->decode($encoded);
         * var_dump($data === $decoded); // bool(true)
         * ```
         * @since 1.0.0
         */
        public function encode(string $data): string {}

        /**
         * Decode string data back to binary using the codec's alphabet
         * Converts a string encoded with this codec back to its original binary form.
         * The string must contain only characters from the codec's alphabet and
         * optional padding characters.
         * 
         * @param string $encoded Encoded string to decode
         * @return string Original binary data
         * @throws Exception If string contains invalid characters or decoding fails
         * 
         * @example
         * ```php
         * $codec = Codec::base64Standard();
         * $encoded = "SGVsbG8gV29ybGQ="; // "Hello World" in Base64
         * $decoded = $codec->decode($encoded);
         * echo $decoded; // "Hello World"
         * // Handle invalid input
         * try {
         * $codec->decode("Invalid@Characters!");
         * } catch (Exception $e) {
         * echo "Decoding failed: " . $e->getMessage();
         * }
         * ```
         * @since 1.0.0
         */
        public function decode(string $encoded): string {}

        /**
         * Create a Base32 codec using RFC 4648 alphabet
         * Returns a codec configured for standard Base32 encoding as defined in RFC 4648.
         * Uses the alphabet A-Z and 2-7 with '=' padding. This is the standard Base32
         * encoding used in many applications.
         * 
         * @return Codec Base32 RFC 4648 codec instance
         * 
         * @example
         * ```php
         * $codec = Codec::base32Rfc4648();
         * $encoded = $codec->encode("Hello World");
         * echo $encoded; // "JBSWY3DPEBLW64TMMQQQ===="
         * $decoded = $codec->decode("JBSWY3DPEBLW64TMMQQQ====");
         * echo $decoded; // "Hello World"
         * ```
         * @since 1.0.0
         */
        public static function base32Rfc4648(?string $padding = NULL): \Php\Encoding\Codec {}

        /**
         * Create a Base32 codec using Crockford alphabet
         * Returns a codec configured for Crockford Base32 encoding. This encoding
         * excludes ambiguous characters (0, 1, I, L, O, U) and is case-insensitive.
         * It's designed to be human-readable and error-resistant.
         * 
         * @return Codec Base32 Crockford codec instance
         * 
         * @example
         * ```php
         * $codec = Codec::base32Crockford();
         * $encoded = $codec->encode("Hello World");
         * echo $encoded; // "91JPRV3F5GG7EVVG91IMKM"
         * // Case-insensitive decoding
         * $decoded1 = $codec->decode("91JPRV3F5GG7EVVG91IMKM");
         * $decoded2 = $codec->decode("91jprv3f5gg7evvg91imkm");
         * var_dump($decoded1 === $decoded2); // bool(true)
         * ```
         * @since 1.0.0
         */
        public static function base32Crockford(?string $padding = NULL): \Php\Encoding\Codec {}

        /**
         * Create a Base58 codec using Bitcoin alphabet
         * Returns a codec configured for Base58 encoding as used by Bitcoin.
         * This encoding excludes confusing characters (0, O, I, l) and produces
         * shorter strings than Base64 while remaining human-readable.
         * 
         * @return Codec Base58 Bitcoin codec instance
         * 
         * @example
         * ```php
         * $codec = Codec::base58Bitcoin();
         * $encoded = $codec->encode("Hello World");
         * echo $encoded; // "JxF12TrwUP45BMd"
         * // Commonly used for Bitcoin addresses and keys
         * $hash = hash('sha256', 'some data', true);
         * $encoded = $codec->encode($hash);
         * echo strlen($encoded); // Shorter than Base64
         * ```
         * @since 1.0.0
         */
        public static function base58Bitcoin(?string $padding = NULL): \Php\Encoding\Codec {}

        /**
         * Create a Base64 codec using standard alphabet
         * Returns a codec configured for standard Base64 encoding as defined in RFC 4648.
         * Uses A-Z, a-z, 0-9, +, / with '=' padding. This is the most common Base64
         * encoding used in email, web, and data storage applications.
         * 
         * @return Codec Base64 standard codec instance
         * 
         * @example
         * ```php
         * $codec = Codec::base64Standard();
         * $encoded = $codec->encode("Hello World");
         * echo $encoded; // "SGVsbG8gV29ybGQ="
         * // Same as PHP's built-in base64_encode
         * $data = "Any binary data";
         * $encoded1 = $codec->encode($data);
         * $encoded2 = base64_encode($data);
         * var_dump($encoded1 === $encoded2); // bool(true)
         * ```
         * @since 1.0.0
         */
        public static function base64Standard(?string $padding = NULL): \Php\Encoding\Codec {}

        /**
         * Create a Base64 codec using URL-safe alphabet
         * Returns a codec configured for URL-safe Base64 encoding. Uses A-Z, a-z, 0-9,
         * -, _ instead of +, / to avoid issues in URLs and filenames. Padding may be
         * omitted in some applications.
         * 
         * @return Codec Base64 URL-safe codec instance
         * 
         * @example
         * ```php
         * $codec = Codec::base64UrlSafe();
         * $encoded = $codec->encode("Hello World");
         * echo $encoded; // "SGVsbG8gV29ybGQ="
         * // Safe for use in URLs and filenames
         * $data = random_bytes(16);
         * $encoded = $codec->encode($data);
         * $url = "https://example.com/data/" . $encoded;
         * // No need to URL-encode the result
         * ```
         * @since 1.0.0
         */
        public static function base64UrlSafe(?string $padding = NULL): \Php\Encoding\Codec {}

        /**
         * Create a Base64 codec using MIME alphabet with line breaks
         * Returns a codec configured for MIME Base64 encoding. Uses the same alphabet
         * as standard Base64 but adds line breaks every 76 characters as required
         * by MIME specifications for email attachments.
         * 
         * @return Codec Base64 MIME codec instance
         * 
         * @example
         * ```php
         * $codec = Codec::base64Mime();
         * $longData = str_repeat("Hello World! ", 20);
         * $encoded = $codec->encode($longData);
         * // Output will have line breaks every 76 characters
         * echo $encoded;
         * // Suitable for email attachments
         * $fileData = file_get_contents('document.pdf');
         * $mimeEncoded = $codec->encode($fileData);
         * // Can be safely included in email body
         * ```
         * @since 1.0.0
         */
        public static function base64Mime(?string $padding = NULL): \Php\Encoding\Codec {}

    }

}

