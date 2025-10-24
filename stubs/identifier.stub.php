<?php

/**
 * PHP Identifier Extension Stubs
 * 
 * This file defines the userland API for 128-bit identifiers (UUIDs and ULIDs)
 */

namespace Php\Identifier;

/**
 * Context interface for controlling time and randomness in identifier generation
 */
interface Context
{
    /**
     * Get current timestamp in milliseconds
     *
     * @return int
     */
    public function getTimestampMs(): int;

    /**
     * Get current timestamp in 100-nanosecond intervals since Gregorian epoch (1582-10-15)
     *
     * @return int
     */
    public function getGregorianEpochTime(): int;

    /**
     * Generate random bytes
     *
     * @param int $length Number of bytes to generate
     * @return string Random bytes
     */
    public function getRandomBytes(int $length): string;
}

namespace Php\Identifier\Context;

use Php\Identifier\Context;

/**
 * System context using real time and cryptographically secure randomness
 */
final class System implements Context
{
    /**
     * Get the singleton system context instance
     *
     * @return self
     */
    public static function getInstance(): self {}

    /**
     * Get current timestamp in milliseconds
     *
     * @return int
     */
    public function getTimestampMs(): int {}

    /**
     * Get current timestamp in 100-nanosecond intervals since Gregorian epoch (1582-10-15)
     *
     * @return int
     */
    public function getGregorianEpochTime(): int {}

    /**
     * Generate cryptographically secure random bytes
     *
     * @param int $length Number of bytes to generate
     * @return string Random bytes
     */
    public function getRandomBytes(int $length): string {}
}

/**
 * Fixed context for deterministic identifier generation (useful for testing)
 */
final class Fixed implements Context
{
    /**
     * Create a fixed context with deterministic time and randomness
     *
     * @param int $timestampMs Fixed timestamp in milliseconds
     * @param int $seed Seed for deterministic randomness
     * @return self
     */
    public static function create(int $timestampMs, int $seed): self {}

    /**
     * Advance the internal timestamp by the specified milliseconds
     *
     * @param int $milliseconds Milliseconds to advance
     * @return self Returns $this for method chaining
     */
    public function advanceTime(int $milliseconds): self {}

    /**
     * Advance the internal timestamp by the specified seconds
     *
     * @param int $seconds Seconds to advance
     * @return self Returns $this for method chaining
     */
    public function advanceTimeSeconds(int $seconds): self {}

    /**
     * Set the timestamp to a specific value
     *
     * @param int $timestampMs New timestamp in milliseconds
     * @return self Returns $this for method chaining
     */
    public function setTimestamp(int $timestampMs): self {}

    /**
     * Get current timestamp in milliseconds
     *
     * @return int
     */
    public function getTimestampMs(): int {}

    /**
     * Get current timestamp in 100-nanosecond intervals since Gregorian epoch (1582-10-15)
     *
     * @return int
     */
    public function getGregorianEpochTime(): int {}

    /**
     * Generate deterministic random bytes based on the seed
     *
     * @param int $length Number of bytes to generate
     * @return string Deterministic random bytes
     */
    public function getRandomBytes(int $length): string {}
}

namespace Php\Identifier;

/**
 * Base class for 128-bit identifiers
 *
 * Holds a 16-byte buffer representing a 128-bit value.
 * Can be instantiated directly or extended by specific identifier types.
 */
class Bit128
{
    /**
     * Create a new 128-bit identifier from raw bytes
     * 
     * @param string $bytes 16 bytes of binary data
     * @throws InvalidArgumentException if bytes length is not 16
     */
    public function __construct(string $bytes) {}

    /**
     * Get the raw 16-byte representation
     *
     * @return string 16 bytes of binary data
     */
    public function getBytes(): string {}

    /**
     * Get the raw 16-byte representation (alias for getBytes)
     *
     * @return string 16 bytes of binary data
     */
    public function toBytes(): string {}

    /**
     * Compare two 128-bit identifiers for equality
     * 
     * @param Bit128 $other
     * @return bool
     */
    public function equals(Bit128 $other): bool {}

    /**
     * Compare two 128-bit identifiers
     * 
     * @param Bit128 $other
     * @return int -1 if less than, 0 if equal, 1 if greater than
     */
    public function compare(Bit128 $other): int {}

    /**
     * Get hexadecimal representation (32 characters, lowercase)
     *
     * @return string
     */
    public function toHex(): string {}

    /**
     * Create from hexadecimal string
     *
     * @param string $hex 32-character hexadecimal string (with or without dashes)
     * @return static
     * @throws InvalidArgumentException if hex is invalid
     */
    public static function fromHex(string $hex): static {}

    /**
     * Create from raw bytes
     *
     * @param string $bytes 16 bytes of binary data
     * @return static
     * @throws InvalidArgumentException if bytes length is not 16
     */
    public static function fromBytes(string $bytes): static {}
}

/**
 * Base class for UUID identifiers
 *
 * Can be instantiated directly for unsupported UUID versions
 */
class Uuid extends Bit128
{
    /**
     * Get the UUID version
     * 
     * @return int Version number (1, 3, 4, 5, 6, or 7)
     */
    public function getVersion(): int {}

    /**
     * Get the UUID variant
     * 
     * @return int Variant value
     */
    public function getVariant(): int {}

    /**
     * Convert to standard UUID string format (8-4-4-4-12)
     * 
     * @return string UUID string with dashes
     */
    public function toString(): string {}

    /**
     * Convert to standard UUID string format (alias for toString)
     * 
     * @return string UUID string with dashes
     */
    public function __toString(): string {}

    /**
     * Create UUID from string representation
     *
     * @param string $uuid UUID string (with or without dashes)
     * @return static
     * @throws InvalidArgumentException if UUID string is invalid
     */
    public static function fromString(string $uuid): static {}

    /**
     * Create UUID from raw bytes
     *
     * @param string $bytes 16 bytes of binary data
     * @return static
     * @throws InvalidArgumentException if bytes length is not 16
     */
    public static function fromBytes(string $bytes): static {}

    /**
     * Create UUID from hexadecimal string
     *
     * @param string $hex 32-character hexadecimal string (with or without dashes)
     * @return static
     * @throws InvalidArgumentException if hex is invalid
     */
    public static function fromHex(string $hex): static {}

    /**
     * Check if this is a nil UUID (all zeros)
     * 
     * @return bool
     */
    public function isNil(): bool {}

    /**
     * Get the nil UUID (all zeros)
     *
     * @return static
     */
    public static function nil(): static {}

    /**
     * Check if this is a max UUID (all ones)
     *
     * @return bool
     */
    public function isMax(): bool {}

    /**
     * Get the max UUID (all ones)
     *
     * @return static
     */
    public static function max(): static {}
}

namespace Php\Identifier\Uuid;

/**
 * UUID Version 1 (Time-based)
 */
final class Version1 extends \Php\Identifier\Uuid
{
    /**
     * Generate a new Version 1 UUID
     *
     * @param string|null $node 6-byte node identifier (MAC address), null for random
     * @param int|null $clockSeq 14-bit clock sequence, null for random
     * @param \Php\Identifier\Context|null $context Context for time and randomness, null for system context
     * @return self
     */
    public static function generate(?string $node = null, ?int $clockSeq = null, ?\Php\Identifier\Context $context = null): self {}

    /**
     * Create Version 1 UUID from string representation
     *
     * @param string $uuid UUID string (with or without dashes)
     * @return self
     * @throws InvalidArgumentException if UUID string is invalid or not version 1
     */
    public static function fromString(string $uuid): self {}

    /**
     * Create Version 1 UUID from raw bytes
     *
     * @param string $bytes 16 bytes of binary data
     * @return self
     * @throws InvalidArgumentException if bytes length is not 16 or not version 1
     */
    public static function fromBytes(string $bytes): self {}

    /**
     * Get the timestamp from the UUID
     * 
     * @return int Unix timestamp in microseconds
     */
    public function getTimestamp(): int {}

    /**
     * Get the node identifier
     * 
     * @return string 6-byte node identifier
     */
    public function getNode(): string {}

    /**
     * Get the clock sequence
     * 
     * @return int 14-bit clock sequence
     */
    public function getClockSequence(): int {}
}

/**
 * UUID Version 3 (Name-based using MD5)
 */
final class Version3 extends \Php\Identifier\Uuid
{
    /**
     * Generate a Version 3 UUID from namespace and name
     *
     * @param \Php\Identifier\Uuid $namespace Namespace UUID
     * @param string $name Name to hash
     * @return self
     */
    public static function generate(\Php\Identifier\Uuid $namespace, string $name): self {}

    /**
     * Create Version 3 UUID from string representation
     *
     * @param string $uuid UUID string (with or without dashes)
     * @return self
     * @throws InvalidArgumentException if UUID string is invalid or not version 3
     */
    public static function fromString(string $uuid): self {}

    /**
     * Create Version 3 UUID from raw bytes
     *
     * @param string $bytes 16 bytes of binary data
     * @return self
     * @throws InvalidArgumentException if bytes length is not 16 or not version 3
     */
    public static function fromBytes(string $bytes): self {}


}

/**
 * UUID Version 4 (Random)
 */
final class Version4 extends \Php\Identifier\Uuid
{
    /**
     * Generate a new random Version 4 UUID
     *
     * @param \Php\Identifier\Context|null $context Context for randomness, null for system context
     * @return self
     */
    public static function generate(?\Php\Identifier\Context $context = null): self {}

    /**
     * Create Version 4 UUID from string representation
     *
     * @param string $uuid UUID string (with or without dashes)
     * @return self
     * @throws InvalidArgumentException if UUID string is invalid or not version 4
     */
    public static function fromString(string $uuid): self {}

    /**
     * Create Version 4 UUID from raw bytes
     *
     * @param string $bytes 16 bytes of binary data
     * @return self
     * @throws InvalidArgumentException if bytes length is not 16 or not version 4
     */
    public static function fromBytes(string $bytes): self {}

    /**
     * Get the random bytes (including version and variant bits)
     *
     * @return string 16 bytes of random data
     */
    public function getRandomBytes(): string {}

    /**
     * Get the pure random bytes (with version and variant bits cleared)
     *
     * @return string 16 bytes of pure random data
     */
    public function getPureRandomBytes(): string {}
}

/**
 * UUID Version 5 (Name-based using SHA-1)
 */
final class Version5 extends \Php\Identifier\Uuid
{
    /**
     * Generate a Version 5 UUID from namespace and name
     *
     * @param \Php\Identifier\Uuid $namespace Namespace UUID
     * @param string $name Name to hash
     * @return self
     */
    public static function generate(\Php\Identifier\Uuid $namespace, string $name): self {}

    /**
     * Create Version 5 UUID from string representation
     *
     * @param string $uuid UUID string (with or without dashes)
     * @return self
     * @throws InvalidArgumentException if UUID string is invalid or not version 5
     */
    public static function fromString(string $uuid): self {}

    /**
     * Create Version 5 UUID from raw bytes
     *
     * @param string $bytes 16 bytes of binary data
     * @return self
     * @throws InvalidArgumentException if bytes length is not 16 or not version 5
     */
    public static function fromBytes(string $bytes): self {}

    /**
     * Create Version 5 UUID from hexadecimal string
     *
     * @param string $hex 32-character hexadecimal string (with or without dashes)
     * @return self
     * @throws InvalidArgumentException if hex is invalid or not version 5
     */
    public static function fromHex(string $hex): self {}


}

/**
 * UUID Version 6 (Reordered time-based)
 */
final class Version6 extends \Php\Identifier\Uuid
{
    /**
     * Generate a new Version 6 UUID
     *
     * @param string|null $node 6-byte node identifier (MAC address), null for random
     * @param int|null $clockSeq 14-bit clock sequence, null for random
     * @param \Php\Identifier\Context|null $context Context for time and randomness, null for system context
     * @return self
     */
    public static function generate(?string $node = null, ?int $clockSeq = null, ?\Php\Identifier\Context $context = null): self {}

    /**
     * Create Version 6 UUID from string representation
     *
     * @param string $uuid UUID string (with or without dashes)
     * @return self
     * @throws InvalidArgumentException if UUID string is invalid or not version 6
     */
    public static function fromString(string $uuid): self {}

    /**
     * Create Version 6 UUID from raw bytes
     *
     * @param string $bytes 16 bytes of binary data
     * @return self
     * @throws InvalidArgumentException if bytes length is not 16 or not version 6
     */
    public static function fromBytes(string $bytes): self {}

    /**
     * Create Version 6 UUID from hexadecimal string
     *
     * @param string $hex 32-character hexadecimal string (with or without dashes)
     * @return self
     * @throws InvalidArgumentException if hex is invalid or not version 6
     */
    public static function fromHex(string $hex): self {}

    /**
     * Get the timestamp from the UUID
     * 
     * @return int Unix timestamp in microseconds
     */
    public function getTimestamp(): int {}

    /**
     * Get the node identifier
     * 
     * @return string 6-byte node identifier
     */
    public function getNode(): string {}

    /**
     * Get the clock sequence
     * 
     * @return int 14-bit clock sequence
     */
    public function getClockSequence(): int {}
}

/**
 * UUID Version 7 (Unix timestamp-based)
 */
final class Version7 extends \Php\Identifier\Uuid
{
    /**
     * Generate a new Version 7 UUID
     *
     * @param \Php\Identifier\Context|null $context Context for time and randomness, null for system context
     * @return self
     */
    public static function generate(?\Php\Identifier\Context $context = null): self {}

    /**
     * Create Version 7 UUID from string representation
     *
     * @param string $uuid UUID string (with or without dashes)
     * @return self
     * @throws InvalidArgumentException if UUID string is invalid or not version 7
     */
    public static function fromString(string $uuid): self {}

    /**
     * Create Version 7 UUID from raw bytes
     *
     * @param string $bytes 16 bytes of binary data
     * @return self
     * @throws InvalidArgumentException if bytes length is not 16 or not version 7
     */
    public static function fromBytes(string $bytes): self {}

    /**
     * Create Version 7 UUID from hexadecimal string
     *
     * @param string $hex 32-character hexadecimal string (with or without dashes)
     * @return self
     * @throws InvalidArgumentException if hex is invalid or not version 7
     */
    public static function fromHex(string $hex): self {}

    /**
     * Get the timestamp from the UUID
     *
     * @return int Unix timestamp in milliseconds
     */
    public function getTimestamp(): int {}

    /**
     * Get the random bytes (10 bytes after timestamp, with version/variant bits cleared)
     *
     * @return string 10 bytes of random data
     */
    public function getRandomBytes(): string {}

    /**
     * Get the random A component (first 12 bits of randomness)
     *
     * @return int 12-bit random value
     */
    public function getRandomA(): int {}

    /**
     * Get the random B component (remaining 62 bits of randomness)
     *
     * @return int 62-bit random value
     */
    public function getRandomB(): int {}
}

/**
 * ULID (Universally Unique Lexicographically Sortable Identifier)
 */
final class Ulid extends \Php\Identifier\Bit128
{
    /**
     * Generate a new ULID
     *
     * @param \Php\Identifier\Context|null $context Context for time and randomness, null for system context
     * @return self
     */
    public static function generate(?\Php\Identifier\Context $context = null): self {}

    /**
     * Convert to ULID string format (26 characters, Crockford Base32)
     * 
     * @return string ULID string
     */
    public function toString(): string {}

    /**
     * Convert to ULID string format (alias for toString)
     * 
     * @return string ULID string
     */
    public function __toString(): string {}

    /**
     * Create ULID from string representation
     *
     * @param string $ulid ULID string (26 characters)
     * @return self
     * @throws InvalidArgumentException if ULID string is invalid
     */
    public static function fromString(string $ulid): self {}

    /**
     * Create ULID from hexadecimal string
     *
     * @param string $hex 32-character hexadecimal string (with or without dashes)
     * @return self
     * @throws InvalidArgumentException if hex is invalid
     */
    public static function fromHex(string $hex): self {}

    /**
     * Create ULID from raw bytes
     *
     * @param string $bytes 16 bytes of binary data
     * @return self
     * @throws InvalidArgumentException if bytes length is not 16
     */
    public static function fromBytes(string $bytes): self {}

    /**
     * Get the timestamp from the ULID
     * 
     * @return int Unix timestamp in milliseconds
     */
    public function getTimestamp(): int {}

    /**
     * Get the random component
     *
     * @return string 10 bytes of random data
     */
    public function getRandomness(): string {}
}

namespace Php\Encoding;

/**
 * High-performance encoding/decoding codec for various base encodings
 *
 * Supports Base32, Base58, and Base64 variants with custom alphabets and padding
 */
final class Codec
{
    /**
     * Base32 RFC 4648 alphabet
     */
    public const BASE32_RFC4648 = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    /**
     * Base32 Crockford alphabet (used by ULID)
     */
    public const BASE32_CROCKFORD = '0123456789ABCDEFGHJKMNPQRSTVWXYZ';

    /**
     * Base58 Bitcoin alphabet
     */
    public const BASE58_BITCOIN = '123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz';

    /**
     * Base64 standard alphabet (RFC 4648)
     */
    public const BASE64_STANDARD = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/';

    /**
     * Base64 URL-safe alphabet (RFC 4648)
     */
    public const BASE64_URLSAFE = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_';

    /**
     * Base64 MIME alphabet
     */
    public const BASE64_MIME = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/';
    /**
     * Create a new codec with the specified alphabet and optional padding
     *
     * @param string $alphabet Encoding alphabet (e.g., Base32, Base58, Base64)
     * @param string|null $padding Padding character (default: '=' for most encodings, null for no padding)
     * @throws InvalidArgumentException if alphabet is empty or padding character is present in alphabet
     */
    public function __construct(string $alphabet, ?string $padding = null) {}

    /**
     * Encode binary data using this codec's alphabet
     *
     * @param string $data Binary data to encode
     * @return string Encoded string
     * @throws InvalidArgumentException if data is invalid
     */
    public function encode(string $data): string {}

    /**
     * Decode encoded string using this codec's alphabet
     *
     * @param string $encoded Encoded string to decode
     * @return string Decoded binary data
     * @throws InvalidArgumentException if encoded string contains invalid characters
     */
    public function decode(string $encoded): string {}

    /**
     * Create a Base32 RFC 4648 codec instance
     *
     * @param string|null $padding Padding character (default: '=')
     * @return self Codec configured for Base32 RFC 4648
     * @throws InvalidArgumentException if padding character is present in alphabet
     */
    public static function base32Rfc4648(?string $padding = null): self {}

    /**
     * Create a Base32 Crockford codec instance (used by ULID)
     *
     * @param string|null $padding Padding character (default: no padding)
     * @return self Codec configured for Base32 Crockford
     * @throws InvalidArgumentException if padding character is present in alphabet
     */
    public static function base32Crockford(?string $padding = null): self {}

    /**
     * Create a Base58 Bitcoin codec instance
     *
     * @param string|null $padding Padding character (default: no padding)
     * @return self Codec configured for Base58 Bitcoin
     * @throws InvalidArgumentException if padding character is present in alphabet
     */
    public static function base58Bitcoin(?string $padding = null): self {}

    /**
     * Create a Base64 standard codec instance
     *
     * @param string|null $padding Padding character (default: '=')
     * @return self Codec configured for Base64 standard
     * @throws InvalidArgumentException if padding character is present in alphabet
     */
    public static function base64Standard(?string $padding = null): self {}

    /**
     * Create a Base64 URL-safe codec instance
     *
     * @param string|null $padding Padding character (default: '=')
     * @return self Codec configured for Base64 URL-safe
     * @throws InvalidArgumentException if padding character is present in alphabet
     */
    public static function base64UrlSafe(?string $padding = null): self {}

    /**
     * Create a Base64 MIME codec instance
     *
     * @param string|null $padding Padding character (default: '=')
     * @return self Codec configured for Base64 MIME
     * @throws InvalidArgumentException if padding character is present in alphabet
     */
    public static function base64Mime(?string $padding = null): self {}
}
