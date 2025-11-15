<?php

/**
 * Stubs for identifier extension
 * 
 * Generated from extension reflection with full C source documentation.
 * 
 * @version 1.0.0
 * @generated 2025-11-15 21:47:50
 */

namespace Identifier
{
    class Context
    {
    }

    class Bit128 implements \Stringable
    {
        public function __construct(string $bytes) {}

        public function getBytes(): string {}

        public function toBytes(): string {}

        public function equals(\Identifier\Bit128 $other): bool {}

        public function compare(\Identifier\Bit128 $other): int {}

        public function toHex(): string {}

        public static function fromHex(string $hex): \Identifier\Bit128 {}

        public static function fromBytes(string $bytes): \Identifier\Bit128 {}

        public function toString(): string {}

        public function __toString(): string {}

    }

    class Uuid extends \Identifier\Bit128 implements \Stringable
    {
        public function getVersion(): int {}

        public function getVariant(): int {}

        public function toString(): string {}

        public static function fromString(string $uuid): \Identifier\Uuid {}

        public static function fromBytes(string $bytes): \Identifier\Uuid {}

        public static function fromHex(string $hex): \Identifier\Uuid {}

        public function isNil(): bool {}

        public static function nil(): \Identifier\Uuid {}

        public function isMax(): bool {}

        public static function max(): \Identifier\Uuid {}

    }

    final class Ulid extends \Identifier\Bit128 implements \Stringable
    {
        public static function generate(?\Identifier\Context $context = NULL): \Identifier\Ulid {}

        public function toString(): string {}

        public static function fromString(string $ulid): \Identifier\Ulid {}

        public static function fromHex(string $hex): \Identifier\Ulid {}

        public static function fromBytes(string $bytes): \Identifier\Ulid {}

        public function getTimestamp(): int {}

        public function getRandomness(): string {}

    }

}

namespace Identifier\Context
{
    class System implements \Identifier\Context
    {
        public static function getInstance(): \Identifier\Context\System {}

        public function getTimestampMs(): int {}

        public function getGregorianEpochTime(): int {}

        public function getRandomBytes(int $length): string {}

    }

    class Fixed implements \Identifier\Context
    {
        public static function create(int $timestamp_ms, int $seed): \Identifier\Context\Fixed {}

        public function advanceTime(int $milliseconds): void {}

        public function advanceTimeSeconds(int $seconds): void {}

        public function setTimestamp(int $timestamp_ms): void {}

        public function getTimestampMs(): int {}

        public function getGregorianEpochTime(): int {}

        public function getRandomBytes(int $length): string {}

    }

}

namespace Identifier\Uuid
{
    final class Version1 extends \Identifier\Uuid implements \Stringable
    {
        public static function generate(?\Identifier\Context $context = NULL): \Identifier\Uuid\Version1 {}

        public static function fromString(string $uuid): \Identifier\Uuid\Version1 {}

        public static function fromBytes(string $bytes): \Identifier\Uuid\Version1 {}

        public static function fromHex(string $hex): \Identifier\Uuid\Version1 {}

        public function getTimestamp(): int {}

        public function getNode(): string {}

        public function getClockSequence(): int {}

    }

    final class Version3 extends \Identifier\Uuid implements \Stringable
    {
        public static function generate(string $namespace, string $name): \Identifier\Uuid\Version3 {}

        public static function fromString(string $uuid): \Identifier\Uuid\Version3 {}

        public static function fromBytes(string $bytes): \Identifier\Uuid\Version3 {}

        public static function fromHex(string $hex): \Identifier\Uuid\Version3 {}

    }

    final class Version4 extends \Identifier\Uuid implements \Stringable
    {
        public static function generate(?\Identifier\Context $context = NULL): \Identifier\Uuid\Version4 {}

        public static function fromString(string $uuid): \Identifier\Uuid\Version4 {}

        public static function fromBytes(string $bytes): \Identifier\Uuid\Version4 {}

        public static function fromHex(string $hex): \Identifier\Uuid\Version4 {}

        public function getRandomBytes(): string {}

        public function getPureRandomBytes(): string {}

    }

    final class Version5 extends \Identifier\Uuid implements \Stringable
    {
        public static function generate(string $namespace, string $name): \Identifier\Uuid\Version5 {}

        public static function fromString(string $uuid): \Identifier\Uuid\Version5 {}

        public static function fromBytes(string $bytes): \Identifier\Uuid\Version5 {}

        public static function fromHex(string $hex): \Identifier\Uuid\Version5 {}

    }

    final class Version6 extends \Identifier\Uuid implements \Stringable
    {
        public static function generate(?\Identifier\Context $context = NULL): \Identifier\Uuid\Version6 {}

        public static function fromString(string $uuid): \Identifier\Uuid\Version6 {}

        public static function fromBytes(string $bytes): \Identifier\Uuid\Version6 {}

        public static function fromHex(string $hex): \Identifier\Uuid\Version6 {}

        public function getTimestamp(): int {}

        public function getNode(): string {}

        public function getClockSequence(): int {}

    }

    final class Version7 extends \Identifier\Uuid implements \Stringable
    {
        public static function generate(?\Identifier\Context $context = NULL): \Identifier\Uuid\Version7 {}

        public static function fromString(string $uuid): \Identifier\Uuid\Version7 {}

        public static function fromBytes(string $bytes): \Identifier\Uuid\Version7 {}

        public static function fromHex(string $hex): \Identifier\Uuid\Version7 {}

        public function getTimestamp(): int {}

        public function getRandomBytes(): string {}

        public function getRandomA(): string {}

        public function getRandomB(): string {}

    }

}

namespace Encoding
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
         * @since 1.0.0
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
         * @since 1.0.0
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
         * @since 1.0.0
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
         * @since 1.0.0
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
         * @since 1.0.0
         */
        public static function base64Mime(?string $padding = NULL): \Encoding\Codec {}

    }

}

