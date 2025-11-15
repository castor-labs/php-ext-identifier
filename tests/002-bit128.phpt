--TEST--
Bit128 class functionality
--SKIPIF--
<?php if (!extension_loaded("identifier")) print "skip"; ?>
--FILE--
<?php
use Identifier\Bit128;

// Test 1: Constructor with 16 bytes
$bytes = hex2bin('0123456789abcdef0123456789abcdef');
$bit128 = new Bit128($bytes);
echo "Constructor: " . ($bit128 instanceof Bit128 ? "SUCCESS" : "FAIL") . "\n";

// Test 2: getBytes method
$result_bytes = $bit128->getBytes();
echo "getBytes length: " . strlen($result_bytes) . "\n";
echo "getBytes matches: " . ($result_bytes === $bytes ? "YES" : "NO") . "\n";

// Test 3: toBytes method (alias for getBytes)
$to_bytes = $bit128->toBytes();
echo "toBytes equals getBytes: " . ($to_bytes === $result_bytes ? "YES" : "NO") . "\n";

// Test 4: toHex method
$hex = $bit128->toHex();
echo "toHex result: " . $hex . "\n";
echo "toHex length: " . strlen($hex) . "\n";

// Test 5: fromHex factory method
$bit128_2 = Bit128::fromHex('fedcba9876543210fedcba9876543210');
echo "fromHex creates Bit128: " . ($bit128_2 instanceof Bit128 ? "YES" : "NO") . "\n";
echo "fromHex result: " . $bit128_2->toHex() . "\n";

// Test 6: fromBytes factory method
$test_bytes = hex2bin('aabbccddeeff00112233445566778899');
$bit128_3 = Bit128::fromBytes($test_bytes);
echo "fromBytes creates Bit128: " . ($bit128_3 instanceof Bit128 ? "YES" : "NO") . "\n";
echo "fromBytes result: " . $bit128_3->toHex() . "\n";

// Test 7: Round-trip consistency
$original_hex = 'deadbeefcafebabe1234567890abcdef';
$from_hex = Bit128::fromHex($original_hex);
$to_hex = $from_hex->toHex();
echo "Round-trip hex: " . ($original_hex === $to_hex ? "SUCCESS" : "FAIL") . "\n";

$original_bytes = hex2bin($original_hex);
$from_bytes = Bit128::fromBytes($original_bytes);
$to_bytes = $from_bytes->toBytes();
echo "Round-trip bytes: " . ($original_bytes === $to_bytes ? "SUCCESS" : "FAIL") . "\n";

// Test 8: equals method
$bit128_a = Bit128::fromHex('1234567890abcdef1234567890abcdef');
$bit128_b = Bit128::fromHex('1234567890abcdef1234567890abcdef');
$bit128_c = Bit128::fromHex('fedcba0987654321fedcba0987654321');
echo "Equal objects: " . ($bit128_a->equals($bit128_b) ? "YES" : "NO") . "\n";
echo "Different objects: " . ($bit128_a->equals($bit128_c) ? "NO" : "YES") . "\n";

// Test 9: compare method
echo "Compare equal: " . $bit128_a->compare($bit128_b) . "\n";
echo "Compare different: " . ($bit128_a->compare($bit128_c) !== 0 ? "NON_ZERO" : "ZERO") . "\n";

// Test 10: Error cases
try {
    new Bit128("short");
    echo "Short bytes error: FAIL\n";
} catch (Exception $e) {
    echo "Short bytes error: SUCCESS\n";
}

try {
    Bit128::fromHex("invalid");
    echo "Invalid hex error: FAIL\n";
} catch (Exception $e) {
    echo "Invalid hex error: SUCCESS\n";
}
?>
--EXPECT--
Constructor: SUCCESS
getBytes length: 16
getBytes matches: YES
toBytes equals getBytes: YES
toHex result: 0123456789abcdef0123456789abcdef
toHex length: 32
fromHex creates Bit128: YES
fromHex result: fedcba9876543210fedcba9876543210
fromBytes creates Bit128: YES
fromBytes result: aabbccddeeff00112233445566778899
Round-trip hex: SUCCESS
Round-trip bytes: SUCCESS
Equal objects: YES
Different objects: YES
Compare equal: 0
Compare different: NON_ZERO
Short bytes error: SUCCESS
Invalid hex error: SUCCESS
