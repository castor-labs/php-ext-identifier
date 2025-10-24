--TEST--
Codec encoding and decoding functionality
--SKIPIF--
<?php if (!extension_loaded('identifier')) print 'skip'; ?>
--FILE--
<?php

use Php\Encoding\Codec;

echo "=== Testing Codec Class ===\n";

// Test data
$testData = "Hello World!";
$binaryData = str_repeat("\x00\x01\x02\x03", 4); // Fixed 16-byte binary data

echo "\n--- Testing Base32 RFC 4648 ---\n";
$base32Rfc = Codec::base32Rfc4648();
$encoded = $base32Rfc->encode($testData);
echo "Encoded: $encoded\n";
$decoded = $base32Rfc->decode($encoded);
echo "Decoded: $decoded\n";
echo "Round-trip success: " . ($testData === $decoded ? "YES" : "NO") . "\n";

echo "\n--- Testing Base32 Crockford ---\n";
$base32Crockford = Codec::base32Crockford();
$encoded = $base32Crockford->encode($testData);
echo "Encoded: $encoded\n";
$decoded = $base32Crockford->decode($encoded);
echo "Decoded: $decoded\n";
echo "Round-trip success: " . ($testData === $decoded ? "YES" : "NO") . "\n";

echo "\n--- Testing Base58 Bitcoin ---\n";
$base58 = Codec::base58Bitcoin();
$encoded = $base58->encode($testData);
echo "Encoded: $encoded\n";
$decoded = $base58->decode($encoded);
echo "Decoded: $decoded\n";
echo "Round-trip success: " . ($testData === $decoded ? "YES" : "NO") . "\n";

echo "\n--- Testing Base64 Standard ---\n";
$base64Std = Codec::base64Standard();
$encoded = $base64Std->encode($testData);
echo "Encoded: $encoded\n";
$decoded = $base64Std->decode($encoded);
echo "Decoded: $decoded\n";
echo "Round-trip success: " . ($testData === $decoded ? "YES" : "NO") . "\n";

// Compare with PHP's built-in base64_encode
$phpEncoded = base64_encode($testData);
echo "Matches PHP base64_encode: " . ($encoded === $phpEncoded ? "YES" : "NO") . "\n";

echo "\n--- Testing Base64 URL-Safe ---\n";
$base64Url = Codec::base64UrlSafe();
$encoded = $base64Url->encode($testData);
echo "Encoded: $encoded\n";
$decoded = $base64Url->decode($encoded);
echo "Decoded: $decoded\n";
echo "Round-trip success: " . ($testData === $decoded ? "YES" : "NO") . "\n";

echo "\n--- Testing Base64 MIME ---\n";
$base64Mime = Codec::base64Mime();
$encoded = $base64Mime->encode($testData);
echo "Encoded: $encoded\n";
$decoded = $base64Mime->decode($encoded);
echo "Decoded: $decoded\n";
echo "Round-trip success: " . ($testData === $decoded ? "YES" : "NO") . "\n";

echo "\n--- Testing Custom Codec ---\n";
$customCodec = new Codec('0123456789ABCDEF'); // Hex alphabet
$encoded = $customCodec->encode($testData);
echo "Custom encoded: $encoded\n";
$decoded = $customCodec->decode($encoded);
echo "Custom decoded: $decoded\n";
echo "Custom round-trip success: " . ($testData === $decoded ? "YES" : "NO") . "\n";

echo "\n--- Testing Custom Codec with Padding ---\n";
$customPadded = new Codec('ABCDEFGHIJKLMNOPQRSTUVWXYZ234567', '*');
$encoded = $customPadded->encode($testData);
echo "Custom padded encoded: $encoded\n";
$decoded = $customPadded->decode($encoded);
echo "Custom padded decoded: $decoded\n";
echo "Custom padded round-trip success: " . ($testData === $decoded ? "YES" : "NO") . "\n";

echo "\n--- Testing Binary Data ---\n";
$base64 = Codec::base64Standard();
$encoded = $base64->encode($binaryData);
echo "Binary data length: " . strlen($binaryData) . "\n";
echo "Encoded length: " . strlen($encoded) . "\n";
$decoded = $base64->decode($encoded);
echo "Binary round-trip success: " . ($binaryData === $decoded ? "YES" : "NO") . "\n";

echo "\n--- Testing Error Handling ---\n";
try {
    new Codec(''); // Empty alphabet
    echo "ERROR: Should have thrown exception for empty alphabet\n";
} catch (Exception $e) {
    echo "Correctly caught exception: " . $e->getMessage() . "\n";
}

try {
    $base64 = Codec::base64Standard();
    $base64->decode('Invalid@Characters!'); // Invalid characters
    echo "ERROR: Should have thrown exception for invalid characters\n";
} catch (Exception $e) {
    echo "Correctly caught decode exception: " . $e->getMessage() . "\n";
}

echo "\n--- Testing Different Data Types ---\n";
$testCases = [
    '',                    // Empty string
    'A',                   // Single character
    'Hello',               // Short string
    str_repeat('X', 100),  // Long string
    "\x00\x01\x02\x03",   // Binary with null bytes
    "Special chars: àáâãäå", // Unicode characters
];

$codec = Codec::base64Standard();
foreach ($testCases as $i => $testCase) {
    $encoded = $codec->encode($testCase);
    $decoded = $codec->decode($encoded);
    $success = ($testCase === $decoded) ? "YES" : "NO";
    echo "Test case $i: $success\n";
}

echo "\n=== All Codec Tests Complete ===\n";

?>
--EXPECT--
=== Testing Codec Class ===

--- Testing Base32 RFC 4648 ---
Encoded: SDFNRWG6ICXN5ZGYZBB
Decoded: Hello World!
Round-trip success: YES

--- Testing Base32 Crockford ---
Encoded: J35DHP6Y82QDXS6RS11
Decoded: Hello World!
Round-trip success: YES

--- Testing Base58 Bitcoin ---
Encoded: 2NEpo7TZRRrLZSi2U
Decoded: Hello World!
Round-trip success: YES

--- Testing Base64 Standard ---
Encoded: SGVsbG8gV29ybGQh
Decoded: Hello World!
Round-trip success: YES
Matches PHP base64_encode: YES

--- Testing Base64 URL-Safe ---
Encoded: SGVsbG8gV29ybGQh
Decoded: Hello World!
Round-trip success: YES

--- Testing Base64 MIME ---
Encoded: SGVsbG8gV29ybGQh
Decoded: Hello World!
Round-trip success: YES

--- Testing Custom Codec ---
Custom encoded: 48656C6C6F20576F726C6421
Custom decoded: Hello World!
Custom round-trip success: YES

--- Testing Custom Codec with Padding ---
Custom padded encoded: SDFNRWG6ICXN5ZGYZBB
Custom padded decoded: Hello World!
Custom padded round-trip success: YES

--- Testing Binary Data ---
Binary data length: 16
Encoded length: 20
Binary round-trip success: YES

--- Testing Error Handling ---
Correctly caught exception: Alphabet cannot be empty
Correctly caught decode exception: Invalid character in encoded string

--- Testing Different Data Types ---
Test case 0: YES
Test case 1: YES
Test case 2: YES
Test case 3: YES
Test case 4: YES
Test case 5: YES

=== All Codec Tests Complete ===
