<?php

/**
 * Stubs for identifier extension
 * 
 * Generated from extension reflection with full C source documentation.
 * 
 * @version 0.1.0
 * @generated 2025-11-16 07:28:37
 */

namespace Identifier
{
    /**
     * Context interface for identifier generation
     * Defines the interface for controlling time and randomness sources during
     * identifier generation. Contexts allow you to customize how timestamps and
     * random bytes are generated, which is particularly useful for testing
     * with deterministic values or for using alternative time sources.
     * Two implementations are provided:
     * - Context\System - Uses real system time and cryptographically secure randomness
     * - Context\Fixed - Uses fixed/deterministic values for reproducible testing
     * 
     * @since 0.1.0
     */
    interface Context
    {
        /**
         * Get the current timestamp in milliseconds
         * Returns the timestamp in milliseconds since Unix epoch (January 1, 1970).
         * This is used for generating time-based identifiers like UUIDs v7 and ULIDs.
         * 
         * @return int Timestamp in milliseconds since Unix epoch
         * @since 0.1.0
         */
        public function getTimestampMs(): int {}

        /**
         * Get the current time as Gregorian epoch time
         * Returns the current time in 100-nanosecond intervals since the Gregorian
         * epoch (October 15, 1582). This is used for UUID v1 and v6 timestamps.
         * 
         * @return int Timestamp in 100-nanosecond intervals since Gregorian epoch
         * @since 0.1.0
         */
        public function getGregorianEpochTime(): int {}

        /**
         * Generate random bytes
         * Returns a string of random bytes. The implementation determines whether
         * these are cryptographically secure (System) or deterministic (Fixed).
         * 
         * @param int $length Number of random bytes to generate (1-1024)
         * @return string Binary string of random bytes
         * @throws Exception If length is out of valid range
         * @since 0.1.0
         */
        public function getRandomBytes(int $length): string {}

    }

    /**
     * Create a new 128-bit identifier from bytes
     * Constructs a new Bit128 instance from exactly 16 bytes of binary data.
     * This is the base class for all 128-bit identifiers in this extension.
     * 
     * @since 0.1.0
     */
    class Bit128 implements \Stringable
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
         * @since 0.1.0
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
         * @since 0.1.0
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
         * @since 0.1.0
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
         * @since 0.1.0
         */
        public function equals(\Identifier\Bit128 $other): bool {}

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
         * @since 0.1.0
         */
        public function compare(\Identifier\Bit128 $other): int {}

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
         * @since 0.1.0
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
         * @since 0.1.0
         */
        public static function fromHex(string $hex): \Identifier\Bit128 {}

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
         * @since 0.1.0
         */
        public static function fromBytes(string $bytes): \Identifier\Bit128 {}

        /**
         * Convert the identifier to a string representation
         * Returns a string representation of the identifier. For the base Bit128 class,
         * this returns the hexadecimal representation. Subclasses like Uuid and Ulid
         * override this method to provide their canonical string formats.
         * 
         * @return string String representation of the identifier
         * 
         * @example
         * ```php
         * $bit128 = Bit128::fromHex('0123456789abcdef0123456789abcdef');
         * echo $bit128->toString(); // "0123456789abcdef0123456789abcdef"
         * ```
         * @since 0.1.0
         */
        public function toString(): string {}

        /**
         * Magic method for string conversion
         * Allows the identifier to be automatically converted to a string when used in
         * string contexts. Delegates to the toString() method which can be overridden by subclasses.
         * 
         * @return string Identifier in canonical string format
         * 
         * @example
         * ```php
         * $uuid = Version4::generate();
         * echo $uuid; // Automatically calls __toString()
         * echo "UUID: $uuid"; // String interpolation
         * ```
         * @since 0.1.0
         */
        public function __toString(): string {}

    }

    /**
     * Get the UUID version number
     * Returns the version number stored in bits 12-15 of the time_hi_and_version field.
     * This indicates which UUID generation algorithm was used.
     * 
     * @since 0.1.0
     */
    class Uuid extends \Identifier\Bit128 implements \Stringable
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
         * @since 0.1.0
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
         * @since 0.1.0
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
         * @since 0.1.0
         */
        public function toString(): string {}

        /**
         * Create a UUID from a string representation
         * Parses a UUID string in the standard format (8-4-4-4-12) and returns
         * the appropriate UUID version object. Automatically detects the version
         * and returns the correct subclass (Version1, Version3, Version4, etc.).
         * 
         * @param string $uuid UUID string in format "xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx"
         * @return Uuid UUID instance of the appropriate version
         * @throws Exception If the string format is invalid
         * 
         * @example
         * ```php
         * $uuid = Uuid::fromString('550e8400-e29b-41d4-a716-446655440000');
         * echo $uuid->getVersion(); // 4
         * echo get_class($uuid); // Identifier\Uuid\Version4
         * ```
         * @since 0.1.0
         */
        public static function fromString(string $uuid): \Identifier\Uuid {}

        /**
         * Create a UUID from binary bytes
         * Creates a UUID from exactly 16 bytes of binary data. Automatically
         * detects the UUID version from the bytes and returns the appropriate
         * version-specific subclass.
         * 
         * @param string $bytes Exactly 16 bytes of binary data
         * @return Uuid UUID instance of the appropriate version
         * @throws Exception If bytes is not exactly 16 bytes
         * 
         * @example
         * ```php
         * $bytes = random_bytes(16);
         * $bytes[6] = chr((ord($bytes[6]) & 0x0F) | 0x40); // Set version 4
         * $bytes[8] = chr((ord($bytes[8]) & 0x3F) | 0x80); // Set variant
         * $uuid = Uuid::fromBytes($bytes);
         * echo $uuid->getVersion(); // 4
         * ```
         * @since 0.1.0
         */
        public static function fromBytes(string $bytes): \Identifier\Uuid {}

        /**
         * Create a UUID from a hexadecimal string
         * Parses a 32-character hexadecimal string (with or without hyphens) and
         * creates a UUID. Automatically detects the version and returns the appropriate
         * version-specific subclass. Case-insensitive.
         * 
         * @param string $hex 32-character hexadecimal string (with or without hyphens)
         * @return Uuid UUID instance of the appropriate version
         * @throws Exception If hex string is invalid
         * 
         * @example
         * ```php
         * $uuid = Uuid::fromHex('550e8400e29b41d4a716446655440000');
         * echo $uuid->toString(); // "550e8400-e29b-41d4-a716-446655440000"
         * // Also works with hyphens
         * $uuid2 = Uuid::fromHex('550e8400-e29b-41d4-a716-446655440000');
         * ```
         * @since 0.1.0
         */
        public static function fromHex(string $hex): \Identifier\Uuid {}

        /**
         * Check if this UUID is the nil UUID
         * Returns true if all 128 bits are zero (00000000-0000-0000-0000-000000000000).
         * The nil UUID is defined in RFC 4122 and represents a special "null" value.
         * 
         * @return bool True if this is the nil UUID, false otherwise
         * 
         * @example
         * ```php
         * $nil = Uuid::nil();
         * var_dump($nil->isNil()); // bool(true)
         * $uuid = Version4::generate();
         * var_dump($uuid->isNil()); // bool(false)
         * ```
         * @since 0.1.0
         */
        public function isNil(): bool {}

        /**
         * Create the nil UUID
         * Returns a UUID with all bits set to zero (00000000-0000-0000-0000-000000000000).
         * This is a special UUID defined in RFC 4122 that represents a null or empty value.
         * 
         * @return Uuid The nil UUID instance
         * 
         * @example
         * ```php
         * $nil = Uuid::nil();
         * echo $nil->toString(); // "00000000-0000-0000-0000-000000000000"
         * var_dump($nil->isNil()); // bool(true)
         * ```
         * @since 0.1.0
         */
        public static function nil(): \Identifier\Uuid {}

        /**
         * Check if this UUID is the max UUID
         * Returns true if all 128 bits are set to 1 (ffffffff-ffff-ffff-ffff-ffffffffffff).
         * The max UUID is defined in RFC 4122 and represents the maximum possible UUID value.
         * 
         * @return bool True if this is the max UUID, false otherwise
         * 
         * @example
         * ```php
         * $max = Uuid::max();
         * var_dump($max->isMax()); // bool(true)
         * $uuid = Version4::generate();
         * var_dump($uuid->isMax()); // bool(false)
         * ```
         * @since 0.1.0
         */
        public function isMax(): bool {}

        /**
         * Create the max UUID
         * Returns a UUID with all bits set to 1 (ffffffff-ffff-ffff-ffff-ffffffffffff).
         * This is a special UUID defined in RFC 4122 that represents the maximum possible UUID value.
         * 
         * @return Uuid The max UUID instance
         * 
         * @example
         * ```php
         * $max = Uuid::max();
         * echo $max->toString(); // "ffffffff-ffff-ffff-ffff-ffffffffffff"
         * var_dump($max->isMax()); // bool(true)
         * ```
         * @since 0.1.0
         */
        public static function max(): \Identifier\Uuid {}

    }

    /**
     * Generate a new ULID (Universally Unique Lexicographically Sortable Identifier)
     * Creates a new ULID with a timestamp component and random component.
     * ULIDs are lexicographically sortable and encode a timestamp, making them
     * ideal for use as database primary keys and distributed system identifiers.
     * 
     * @since 0.1.0
     */
    final class Ulid extends \Identifier\Bit128 implements \Stringable
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
         * @since 0.1.0
         */
        public static function generate(?\Identifier\Context $context = NULL): \Identifier\Ulid {}

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
         * @since 0.1.0
         */
        public function toString(): string {}

        /**
         * Create a ULID from a string representation
         * Parses a 26-character ULID string in Crockford Base32 encoding and returns
         * a ULID object. The string must be exactly 26 characters and contain only
         * valid Crockford Base32 characters (0-9, A-Z excluding I, L, O, U).
         * 
         * @param string $ulid ULID string in Crockford Base32 format (26 characters)
         * @return Ulid ULID instance
         * @throws Exception If the string format is invalid or contains invalid characters
         * 
         * @example
         * ```php
         * $ulid = Ulid::fromString('01ARZ3NDEKTSV4RRFFQ69G5FAV');
         * echo $ulid->toString(); // "01ARZ3NDEKTSV4RRFFQ69G5FAV"
         * // Case-insensitive parsing
         * $ulid = Ulid::fromString('01arz3ndektsv4rrffq69g5fav');
         * ```
         * @since 0.1.0
         */
        public static function fromString(string $ulid): \Identifier\Ulid {}

        /**
         * Create a ULID from a hexadecimal string
         * Parses a 32-character hexadecimal string (with or without dashes) and returns
         * a ULID object. This is useful for working with ULIDs in their raw hex form.
         * 
         * @param string $hex Hexadecimal string (32 characters, optionally with dashes)
         * @return Ulid ULID instance
         * @throws Exception If the hex string is invalid or has incorrect length
         * 
         * @example
         * ```php
         * // Parse hex string without dashes
         * $ulid = Ulid::fromHex('0188bac7b8de4c4aaa5f8c3e0cd5e5e3');
         * // Parse hex string with dashes
         * $ulid = Ulid::fromHex('0188bac7-b8de-4c4a-aa5f-8c3e0cd5e5e3');
         * echo $ulid->toString(); // Crockford Base32 representation
         * ```
         * @since 0.1.0
         */
        public static function fromHex(string $hex): \Identifier\Ulid {}

        /**
         * Create a ULID from raw bytes
         * Creates a ULID from a 16-byte binary string. This is the most direct way
         * to construct a ULID from its binary representation.
         * 
         * @param string $bytes Binary string of exactly 16 bytes
         * @return Ulid ULID instance
         * @throws Exception If the byte string is not exactly 16 bytes
         * 
         * @example
         * ```php
         * // Create from binary data
         * $bytes = random_bytes(16);
         * $ulid = Ulid::fromBytes($bytes);
         * // Round-trip conversion
         * $ulid1 = Ulid::generate();
         * $ulid2 = Ulid::fromBytes($ulid1->getBytes());
         * var_dump($ulid1->toString() === $ulid2->toString()); // bool(true)
         * ```
         * @since 0.1.0
         */
        public static function fromBytes(string $bytes): \Identifier\Ulid {}

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
         * @since 0.1.0
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
         * @since 0.1.0
         */
        public function getRandomness(): string {}

    }

}

namespace Identifier\Context
{
    class System implements \Identifier\Context
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
         * @since 0.1.0
         */
        public static function getInstance(): \Identifier\Context\System {}

        /**
         * Get the current system time in milliseconds
         * Returns the current Unix timestamp in milliseconds. This is used for
         * generating time-based identifiers like UUIDs v1, v6, v7 and ULIDs.
         * 
         * @return int Current timestamp in milliseconds since Unix epoch
         * 
         * @example
         * ```php
         * $context = System::getInstance();
         * $timestamp = $context->getTimestampMs();
         * echo date('Y-m-d H:i:s.', $timestamp / 1000) . ($timestamp % 1000);
         * ```
         * @since 0.1.0
         */
        public function getTimestampMs(): int {}

        /**
         * Get the current time as Gregorian epoch time
         * Returns the current time in 100-nanosecond intervals since the Gregorian
         * epoch (October 15, 1582). This is used for UUID v1 and v6 timestamps.
         * 
         * @return int Timestamp in 100-nanosecond intervals since Gregorian epoch
         * 
         * @example
         * ```php
         * $context = System::getInstance();
         * $gregorian = $context->getGregorianEpochTime();
         * // Convert back to Unix timestamp
         * $unix_ns = ($gregorian - 122192928000000000) * 100;
         * ```
         * @since 0.1.0
         */
        public function getGregorianEpochTime(): int {}

        /**
         * Generate cryptographically secure random bytes
         * Returns a string of random bytes using the system's cryptographically
         * secure random number generator (CSPRNG). This is used for generating
         * random components of identifiers.
         * 
         * @param int $length Number of random bytes to generate (1-1024)
         * @return string Binary string of random bytes
         * @throws Exception If length is out of valid range
         * 
         * @example
         * ```php
         * $context = System::getInstance();
         * $randomBytes = $context->getRandomBytes(16);
         * echo bin2hex($randomBytes); // 32-character hex string
         * ```
         * @since 0.1.0
         */
        public function getRandomBytes(int $length): string {}

    }

    class Fixed implements \Identifier\Context
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
         * @since 0.1.0
         */
        public static function create(int $timestamp_ms, int $seed): \Identifier\Context\Fixed {}

        /**
         * Advance the context time by milliseconds
         * Increments the internal timestamp by the specified number of milliseconds.
         * This is useful for testing time-based identifiers and simulating the
         * passage of time.
         * 
         * @param int $milliseconds Number of milliseconds to advance
         * @return Fixed Returns $this for method chaining
         * 
         * @example
         * ```php
         * $context = Fixed::create(1640995200000, 12345);
         * $ulid1 = Ulid::generate($context);
         * // Advance time by 1 second
         * $context->advanceTime(1000);
         * $ulid2 = Ulid::generate($context);
         * var_dump($ulid1->getTimestamp() < $ulid2->getTimestamp()); // bool(true)
         * ```
         * @since 0.1.0
         */
        public function advanceTime(int $milliseconds): void {}

        /**
         * Advance the context time by seconds
         * Increments the internal timestamp by the specified number of seconds.
         * This is a convenience method equivalent to calling advanceTime($seconds * 1000).
         * 
         * @param int $seconds Number of seconds to advance
         * @return Fixed Returns $this for method chaining
         * 
         * @example
         * ```php
         * $context = Fixed::create(1640995200000, 12345);
         * // Advance time by 1 hour
         * $context->advanceTimeSeconds(3600);
         * // Method chaining
         * $context->advanceTimeSeconds(60)->advanceTime(500);
         * ```
         * @since 0.1.0
         */
        public function advanceTimeSeconds(int $seconds): void {}

        /**
         * Set the context timestamp to a specific value
         * Sets the internal timestamp to an exact value in milliseconds since Unix epoch.
         * This allows jumping to any point in time for testing purposes.
         * 
         * @param int $timestamp_ms Timestamp in milliseconds since Unix epoch
         * @return Fixed Returns $this for method chaining
         * 
         * @example
         * ```php
         * $context = Fixed::create(1640995200000, 12345);
         * // Jump to a specific date (2023-01-01 00:00:00 UTC)
         * $context->setTimestamp(1672531200000);
         * $uuid = Version7::generate($context);
         * // UUID will have timestamp from 2023-01-01
         * ```
         * @since 0.1.0
         */
        public function setTimestamp(int $timestamp_ms): void {}

        /**
         * Get the current fixed timestamp in milliseconds
         * Returns the internal fixed timestamp value in milliseconds since Unix epoch.
         * This value can be modified using advanceTime(), advanceTimeSeconds(), or setTimestamp().
         * 
         * @return int Timestamp in milliseconds since Unix epoch
         * 
         * @example
         * ```php
         * $context = Fixed::create(1640995200000, 12345);
         * echo $context->getTimestampMs(); // 1640995200000
         * $context->advanceTime(5000);
         * echo $context->getTimestampMs(); // 1640995205000
         * ```
         * @since 0.1.0
         */
        public function getTimestampMs(): int {}

        /**
         * Get the fixed timestamp as Gregorian epoch time
         * Converts the internal timestamp to 100-nanosecond intervals since the
         * Gregorian epoch (October 15, 1582). This is used for UUID v1 and v6 timestamps.
         * 
         * @return int Timestamp in 100-nanosecond intervals since Gregorian epoch
         * 
         * @example
         * ```php
         * $context = Fixed::create(1640995200000, 12345);
         * $gregorian = $context->getGregorianEpochTime();
         * // Returns timestamp suitable for UUID v1/v6
         * ```
         * @since 0.1.0
         */
        public function getGregorianEpochTime(): int {}

        /**
         * Generate deterministic pseudo-random bytes
         * Returns a string of pseudo-random bytes using a seeded Mersenne Twister
         * generator. The output is deterministic based on the seed provided during
         * context creation. This is useful for testing and generating reproducible identifiers.
         * 
         * @param int $length Number of random bytes to generate (1-1024)
         * @return string Binary string of pseudo-random bytes
         * @throws Exception If length is out of valid range
         * 
         * @example
         * ```php
         * $context = Fixed::create(1640995200000, 12345);
         * $bytes1 = $context->getRandomBytes(16);
         * $bytes2 = $context->getRandomBytes(16);
         * // bytes1 and bytes2 will be different but deterministic
         * // Same seed produces same sequence
         * $context2 = Fixed::create(1640995200000, 12345);
         * $bytes3 = $context2->getRandomBytes(16);
         * var_dump($bytes1 === $bytes3); // bool(true)
         * ```
         * @since 0.1.0
         */
        public function getRandomBytes(int $length): string {}

    }

}

namespace Identifier\Uuid
{
    final class Version1 extends \Identifier\Uuid implements \Stringable
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
         * @since 0.1.0
         */
        public static function generate(?\Identifier\Context $context = NULL): \Identifier\Uuid\Version1 {}

        public static function fromString(string $uuid): \Identifier\Uuid\Version1 {}

        public static function fromBytes(string $bytes): \Identifier\Uuid\Version1 {}

        public static function fromHex(string $hex): \Identifier\Uuid\Version1 {}

        /**
         * Get the timestamp from the UUID
         * Extracts the 60-bit timestamp from the UUID version 1 and converts it
         * to milliseconds since Unix epoch.
         * 
         * @return int Timestamp in milliseconds since Unix epoch
         * 
         * @example
         * ```php
         * $uuid = Version1::generate();
         * $timestamp = $uuid->getTimestamp();
         * echo date('Y-m-d H:i:s', $timestamp / 1000);
         * ```
         * @since 0.1.0
         */
        public function getTimestamp(): int {}

        /**
         * Get the node (MAC address) from the UUID
         * Extracts the 48-bit node identifier from the UUID version 1.
         * This is typically derived from the network card's MAC address.
         * 
         * @return string 6-byte binary node identifier
         * 
         * @example
         * ```php
         * $uuid = Version1::generate();
         * $node = $uuid->getNode();
         * echo bin2hex($node); // e.g., "00c04fd430c8"
         * ```
         * @since 0.1.0
         */
        public function getNode(): string {}

        /**
         * Get the clock sequence from the UUID
         * Extracts the 14-bit clock sequence from the UUID version 1.
         * The clock sequence helps ensure uniqueness when the timestamp goes backwards
         * or when the node ID changes.
         * 
         * @return int Clock sequence value (0-16383)
         * 
         * @example
         * ```php
         * $uuid = Version1::generate();
         * $clockSeq = $uuid->getClockSequence();
         * echo $clockSeq; // e.g., 12345
         * ```
         * @since 0.1.0
         */
        public function getClockSequence(): int {}

    }

    final class Version3 extends \Identifier\Uuid implements \Stringable
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
         * @since 0.1.0
         */
        public static function generate(string $namespace, string $name): \Identifier\Uuid\Version3 {}

        public static function fromString(string $uuid): \Identifier\Uuid\Version3 {}

        public static function fromBytes(string $bytes): \Identifier\Uuid\Version3 {}

        public static function fromHex(string $hex): \Identifier\Uuid\Version3 {}

    }

    final class Version4 extends \Identifier\Uuid implements \Stringable
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
         * @since 0.1.0
         */
        public static function generate(?\Identifier\Context $context = NULL): \Identifier\Uuid\Version4 {}

        /**
         * Create a Version 4 UUID from a string representation
         * Parses a UUID string in the standard format (8-4-4-4-12) and validates that
         * it is a valid Version 4 UUID before creating the object.
         * 
         * @param string $uuid UUID string in format "xxxxxxxx-xxxx-4xxx-xxxx-xxxxxxxxxxxx"
         * @return Version4 Version 4 UUID instance
         * @throws Exception If the string format is invalid or not version 4
         * 
         * @example
         * ```php
         * $uuid = Version4::fromString('f47ac10b-58cc-4372-a567-0e02b2c3d479');
         * echo $uuid->getVersion(); // 4
         * ```
         * @since 0.1.0
         */
        public static function fromString(string $uuid): \Identifier\Uuid\Version4 {}

        /**
         * Create a Version 4 UUID from raw bytes
         * Creates a Version 4 UUID from a 16-byte binary string. Validates that the
         * bytes represent a valid Version 4 UUID.
         * 
         * @param string $bytes Binary string of exactly 16 bytes
         * @return Version4 Version 4 UUID instance
         * @throws Exception If the byte string is not exactly 16 bytes or not version 4
         * 
         * @example
         * ```php
         * $bytes = random_bytes(16);
         * // Manually set version and variant bits
         * $bytes[6] = chr((ord($bytes[6]) & 0x0F) | 0x40);
         * $bytes[8] = chr((ord($bytes[8]) & 0x3F) | 0x80);
         * $uuid = Version4::fromBytes($bytes);
         * ```
         * @since 0.1.0
         */
        public static function fromBytes(string $bytes): \Identifier\Uuid\Version4 {}

        /**
         * Create a Version 4 UUID from a hexadecimal string
         * Parses a 32-character hexadecimal string (with or without dashes) and validates
         * that it represents a valid Version 4 UUID.
         * 
         * @param string $hex Hexadecimal string (32 characters, optionally with dashes)
         * @return Version4 Version 4 UUID instance
         * @throws Exception If the hex string is invalid or not version 4
         * 
         * @example
         * ```php
         * // Parse hex without dashes
         * $uuid = Version4::fromHex('f47ac10b58cc4372a5670e02b2c3d479');
         * // Parse hex with dashes
         * $uuid = Version4::fromHex('f47ac10b-58cc-4372-a567-0e02b2c3d479');
         * ```
         * @since 0.1.0
         */
        public static function fromHex(string $hex): \Identifier\Uuid\Version4 {}

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
         * @since 0.1.0
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
         * @since 0.1.0
         */
        public function getPureRandomBytes(): string {}

    }

    final class Version5 extends \Identifier\Uuid implements \Stringable
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
         * @since 0.1.0
         */
        public static function generate(string $namespace, string $name): \Identifier\Uuid\Version5 {}

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
         * @since 0.1.0
         */
        public static function fromString(string $uuid): \Identifier\Uuid\Version5 {}

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
         * @since 0.1.0
         */
        public static function fromBytes(string $bytes): \Identifier\Uuid\Version5 {}

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
         * @since 0.1.0
         */
        public static function fromHex(string $hex): \Identifier\Uuid\Version5 {}

    }

    final class Version6 extends \Identifier\Uuid implements \Stringable
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
         * @since 0.1.0
         */
        public static function generate(?\Identifier\Context $context = NULL): \Identifier\Uuid\Version6 {}

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
         * @since 0.1.0
         */
        public static function fromString(string $uuid): \Identifier\Uuid\Version6 {}

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
         * @since 0.1.0
         */
        public static function fromBytes(string $bytes): \Identifier\Uuid\Version6 {}

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
         * @since 0.1.0
         */
        public static function fromHex(string $hex): \Identifier\Uuid\Version6 {}

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
         * @since 0.1.0
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
         * @since 0.1.0
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
         * @since 0.1.0
         */
        public function getClockSequence(): int {}

    }

    final class Version7 extends \Identifier\Uuid implements \Stringable
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
         * @since 0.1.0
         */
        public static function generate(?\Identifier\Context $context = NULL): \Identifier\Uuid\Version7 {}

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
         * @since 0.1.0
         */
        public static function fromString(string $uuid): \Identifier\Uuid\Version7 {}

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
         * @since 0.1.0
         */
        public static function fromBytes(string $bytes): \Identifier\Uuid\Version7 {}

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
         * @since 0.1.0
         */
        public static function fromHex(string $hex): \Identifier\Uuid\Version7 {}

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
         * @since 0.1.0
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
         * @since 0.1.0
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
         * @since 0.1.0
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
         * @since 0.1.0
         */
        public function getRandomB(): string {}

    }

}

namespace Encoding
{
    /**
     * Codec class for encoding and decoding binary data
     * Provides flexible encoding/decoding functionality with support for various
     * alphabets including Base32, Base58, and Base64 variants. This is primarily
     * used for ULID string encoding (Crockford Base32) but can be used with any
     * custom alphabet.
     * Common use cases:
     * - Base32 RFC4648 encoding (standard Base32)
     * - Base32 Crockford encoding (used by ULIDs, excludes ambiguous characters)
     * - Base58 Bitcoin encoding (no 0, O, I, l to avoid confusion)
     * - Base64 variants (standard, URL-safe, MIME)
     * 
     * @since 0.1.0
     */
    class Codec
    {
        /** Binary alphabet (0-1) for base-2 encoding */
        public const BINARY = '01';
        /** Hexadecimal alphabet (0-9, A-F) for base-16 encoding */
        public const HEXADECIMAL = '0123456789ABCDEF';
        /** Standard Base32 alphabet as defined in RFC 4648 (A-Z, 2-7) */
        public const BASE32_RFC4648 = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        /** Crockford Base32 alphabet (0-9, A-Z excluding I, L, O, U) - used by ULIDs */
        public const BASE32_CROCKFORD = '0123456789ABCDEFGHJKMNPQRSTVWXYZ';
        /** Bitcoin Base58 alphabet (excludes 0, O, I, l to avoid confusion) */
        public const BASE58_BITCOIN = '123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz';
        /** Standard Base64 alphabet (A-Z, a-z, 0-9, +, /) as defined in RFC 4648 */
        public const BASE64_STANDARD = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/';
        /** URL-safe Base64 alphabet (A-Z, a-z, 0-9, -, _) for use in URLs and filenames */
        public const BASE64_URLSAFE = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_';
        /** MIME Base64 alphabet (same as standard but with line breaks every 76 characters) */
        public const BASE64_MIME = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/';

        /**
         * Codec class for encoding and decoding binary data
         * Provides flexible encoding/decoding functionality with support for various
         * alphabets including Base32, Base58, and Base64 variants. This is primarily
         * used for ULID string encoding (Crockford Base32) but can be used with any
         * custom alphabet.
         * Common use cases:
         * - Base32 RFC4648 encoding (standard Base32)
         * - Base32 Crockford encoding (used by ULIDs, excludes ambiguous characters)
         * - Base58 Bitcoin encoding (no 0, O, I, l to avoid confusion)
         * - Base64 variants (standard, URL-safe, MIME)
         * 
         * 
         * @example
         * ```php
         * // Use predefined Crockford Base32 (for ULIDs)
         * $codec = Codec::base32Crockford();
         * $encoded = $codec->encode(random_bytes(16));
         * $decoded = $codec->decode($encoded);
         * // Create custom alphabet
         * $codec = new Codec('0123456789ABCDEF', null); // Hex encoding
         * $hex = $codec->encode($binaryData);
         * ```
         * @since 0.1.0
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
         * @since 0.1.0
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
         * @since 0.1.0
         */
        public function decode(string $encoded): string {}

        /**
         * Create a Binary codec
         * Returns a codec configured for Binary encoding using the alphabet "01".
         * This encoding represents data in base-2 format, the most fundamental
         * numerical representation.
         * 
         * @return Codec Binary codec instance
         * 
         * @example
         * ```php
         * $codec = Codec::binary();
         * $encoded = $codec->encode("Hi");
         * echo $encoded; // "0100100001101001"
         * $decoded = $codec->decode("0100100001101001");
         * echo $decoded; // "Hi"
         * ```
         * @since 0.1.0
         */
        public static function binary(?string $padding = NULL): \Encoding\Codec {}

        /**
         * Create a Hexadecimal codec
         * Returns a codec configured for Hexadecimal (base-16) encoding using the
         * alphabet "0123456789ABCDEF". This is one of the most common encodings
         * for representing binary data in a human-readable format.
         * 
         * @return Codec Hexadecimal codec instance
         * 
         * @example
         * ```php
         * $codec = Codec::hexadecimal();
         * $encoded = $codec->encode("Hello");
         * echo $encoded; // "48656C6C6F"
         * $decoded = $codec->decode("48656C6C6F");
         * echo $decoded; // "Hello"
         * ```
         * @since 0.1.0
         */
        public static function hexadecimal(?string $padding = NULL): \Encoding\Codec {}

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
         * @since 0.1.0
         */
        public static function base32Rfc4648(?string $padding = NULL): \Encoding\Codec {}

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
         * @since 0.1.0
         */
        public static function base32Crockford(?string $padding = NULL): \Encoding\Codec {}

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
         * @since 0.1.0
         */
        public static function base58Bitcoin(?string $padding = NULL): \Encoding\Codec {}

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
         * @since 0.1.0
         */
        public static function base64Standard(?string $padding = NULL): \Encoding\Codec {}

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
         * @since 0.1.0
         */
        public static function base64UrlSafe(?string $padding = NULL): \Encoding\Codec {}

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
         * @since 0.1.0
         */
        public static function base64Mime(?string $padding = NULL): \Encoding\Codec {}

    }

}

