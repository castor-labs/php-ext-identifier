--TEST--
Codec improvements: Binary, Hexadecimal, and validations
--SKIPIF--
<?php if (!extension_loaded('identifier')) print 'skip'; ?>
--FILE--
<?php

use Encoding\Codec;

echo "=== Testing Codec Improvements ===\n";

// Test data
$testData = "Hello";
$binaryData = "\x48\x69"; // "Hi" in ASCII

echo "\n--- Testing Binary Codec ---\n";
$binary = Codec::binary();
$encoded = $binary->encode($binaryData);
echo "Binary encoded: $encoded\n";
$decoded = $binary->decode($encoded);
echo "Binary decoded matches: " . ($binaryData === $decoded ? "YES" : "NO") . "\n";

// Test with "Hi" (0x48 0x69) - note: leading zeros are not preserved in base conversion
$expectedBinary = "100100001101001"; // 0x4869 as binary number
echo "Expected binary: $expectedBinary\n";
echo "Binary encoding correct: " . ($encoded === $expectedBinary ? "YES" : "NO") . "\n";

echo "\n--- Testing Hexadecimal Codec ---\n";
$hex = Codec::hexadecimal();
$encoded = $hex->encode($testData);
echo "Hex encoded: $encoded\n";
$decoded = $hex->decode($encoded);
echo "Hex decoded: $decoded\n";
echo "Hex round-trip success: " . ($testData === $decoded ? "YES" : "NO") . "\n";

// Test known hex encoding
$expectedHex = "48656C6C6F"; // "Hello" in hex
echo "Expected hex: $expectedHex\n";
echo "Hex encoding correct: " . ($encoded === $expectedHex ? "YES" : "NO") . "\n";

echo "\n--- Testing Alphabet Constants ---\n";
echo "BINARY constant: " . Codec::BINARY . "\n";
echo "HEXADECIMAL constant: " . Codec::HEXADECIMAL . "\n";
echo "BINARY length: " . strlen(Codec::BINARY) . "\n";
echo "HEXADECIMAL length: " . strlen(Codec::HEXADECIMAL) . "\n";

echo "\n--- Testing Alphabet Length Validation (must be multiple of 2) ---\n";
try {
    new Codec('ABC'); // Length 3 - not a multiple of 2
    echo "ERROR: Should have thrown exception for odd-length alphabet\n";
} catch (Exception $e) {
    echo "Correctly caught exception: " . $e->getMessage() . "\n";
}

try {
    new Codec('ABCDE'); // Length 5 - not a multiple of 2
    echo "ERROR: Should have thrown exception for odd-length alphabet\n";
} catch (Exception $e) {
    echo "Correctly caught exception: " . $e->getMessage() . "\n";
}

try {
    new Codec('AB'); // Length 2 - valid (multiple of 2)
    echo "Length 2 alphabet: VALID\n";
} catch (Exception $e) {
    echo "ERROR: Should not have thrown exception for length 2 alphabet\n";
}

try {
    new Codec('ABCD'); // Length 4 - valid (multiple of 2)
    echo "Length 4 alphabet: VALID\n";
} catch (Exception $e) {
    echo "ERROR: Should not have thrown exception for length 4 alphabet\n";
}

echo "\n--- Testing Duplicate Character Validation ---\n";
try {
    new Codec('AABB'); // Duplicate characters
    echo "ERROR: Should have thrown exception for duplicate characters\n";
} catch (Exception $e) {
    echo "Correctly caught exception: " . $e->getMessage() . "\n";
}

try {
    new Codec('0123456789012345'); // Has duplicate '0', '1', '2', '3', '4', '5'
    echo "ERROR: Should have thrown exception for duplicate characters\n";
} catch (Exception $e) {
    echo "Correctly caught exception: " . $e->getMessage() . "\n";
}

try {
    new Codec('ABCDEFGH'); // No duplicates - valid
    echo "No duplicates alphabet: VALID\n";
} catch (Exception $e) {
    echo "ERROR: Should not have thrown exception for no-duplicate alphabet\n";
}

echo "\n--- Testing Multiple Validations Together ---\n";
try {
    new Codec('AAA'); // Odd length AND duplicates
    echo "ERROR: Should have thrown exception\n";
} catch (Exception $e) {
    // Should fail on length check first
    echo "Multiple violations caught: " . $e->getMessage() . "\n";
}

echo "\n--- Testing Binary with Various Data ---\n";
$binaryCodec = Codec::binary();
$testCases = [
    "\x00" => "0",  // Single zero byte
    "\x01" => "1",  // Single one
    "\xFF" => "11111111",  // All ones (255)
    "\x01\x00" => "100000000",  // 256 in binary
];

foreach ($testCases as $input => $expected) {
    $encoded = $binaryCodec->encode($input);
    $matches = ($encoded === $expected) ? "YES" : "NO ($encoded)";
    echo "Binary test: $matches\n";
}

echo "\n--- Testing Hexadecimal with Various Data ---\n";
$hexCodec = Codec::hexadecimal();
$testCases = [
    "\x00" => "0",  // Single zero
    "\x0F" => "F",  // Single hex digit
    "\xFF" => "FF",  // Two hex digits
    "\xDE\xAD\xBE\xEF" => "DEADBEEF",  // Four bytes
];

foreach ($testCases as $input => $expected) {
    $encoded = $hexCodec->encode($input);
    $matches = ($encoded === $expected) ? "YES" : "NO ($encoded)";
    echo "Hex test: $matches\n";
}

echo "\n--- Testing Round-trip with Binary ---\n";
$testData = random_bytes(16);
$encoded = $binaryCodec->encode($testData);
$decoded = $binaryCodec->decode($encoded);
echo "Binary round-trip 16 bytes: " . ($testData === $decoded ? "YES" : "NO") . "\n";

echo "\n--- Testing Round-trip with Hexadecimal ---\n";
$testData = random_bytes(16);
$encoded = $hexCodec->encode($testData);
$decoded = $hexCodec->decode($encoded);
echo "Hex round-trip 16 bytes: " . ($testData === $decoded ? "YES" : "NO") . "\n";

echo "\n=== All Codec Improvement Tests Complete ===\n";

?>
--EXPECT--
=== Testing Codec Improvements ===

--- Testing Binary Codec ---
Binary encoded: 100100001101001
Binary decoded matches: YES
Expected binary: 100100001101001
Binary encoding correct: YES

--- Testing Hexadecimal Codec ---
Hex encoded: 48656C6C6F
Hex decoded: Hello
Hex round-trip success: YES
Expected hex: 48656C6C6F
Hex encoding correct: YES

--- Testing Alphabet Constants ---
BINARY constant: 01
HEXADECIMAL constant: 0123456789ABCDEF
BINARY length: 2
HEXADECIMAL length: 16

--- Testing Alphabet Length Validation (must be multiple of 2) ---
Correctly caught exception: Alphabet length must be a multiple of 2
Correctly caught exception: Alphabet length must be a multiple of 2
Length 2 alphabet: VALID
Length 4 alphabet: VALID

--- Testing Duplicate Character Validation ---
Correctly caught exception: Alphabet cannot contain duplicate characters
Correctly caught exception: Alphabet cannot contain duplicate characters
No duplicates alphabet: VALID

--- Testing Multiple Validations Together ---
Multiple violations caught: Alphabet length must be a multiple of 2

--- Testing Binary with Various Data ---
Binary test: YES
Binary test: YES
Binary test: YES
Binary test: YES

--- Testing Hexadecimal with Various Data ---
Hex test: YES
Hex test: YES
Hex test: YES
Hex test: YES

--- Testing Round-trip with Binary ---
Binary round-trip 16 bytes: YES

--- Testing Round-trip with Hexadecimal ---
Hex round-trip 16 bytes: YES

=== All Codec Improvement Tests Complete ===
