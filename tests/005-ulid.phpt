--TEST--
ULID class functionality
--SKIPIF--
<?php if (!extension_loaded("identifier")) print "skip"; ?>
--FILE--
<?php
use Php\Identifier\Ulid;
use Php\Identifier\Context\Fixed;

// Test 1: Generate ULID
$ulid = Ulid::generate();
echo "ULID generated: " . ($ulid instanceof Ulid ? "YES" : "NO") . "\n";

// Test 2: ULID string format (26 characters)
$ulid_string = $ulid->toString();
echo "ULID string length: " . strlen($ulid_string) . "\n";
echo "ULID string format: " . (preg_match('/^[0-9A-Z]{26}$/', $ulid_string) ? "VALID" : "INVALID") . "\n";

// Test 3: __toString magic method
$magic_string = (string) $ulid;
echo "__toString works: " . ($magic_string === $ulid_string ? "YES" : "NO") . "\n";

// Test 4: fromString method
$ulid_from_string = Ulid::fromString($ulid_string);
echo "fromString creates ULID: " . ($ulid_from_string instanceof Ulid ? "YES" : "NO") . "\n";
echo "fromString matches: " . ($ulid_from_string->toString() === $ulid_string ? "YES" : "NO") . "\n";

// Test 5: fromBytes method
$ulid_bytes = $ulid->toBytes();
$ulid_from_bytes = Ulid::fromBytes($ulid_bytes);
echo "fromBytes creates ULID: " . ($ulid_from_bytes instanceof Ulid ? "YES" : "NO") . "\n";
echo "fromBytes matches: " . ($ulid_from_bytes->toString() === $ulid_string ? "YES" : "NO") . "\n";

// Test 6: fromHex method
$ulid_hex = $ulid->toHex();
$ulid_from_hex = Ulid::fromHex($ulid_hex);
echo "fromHex creates ULID: " . ($ulid_from_hex instanceof Ulid ? "YES" : "NO") . "\n";
echo "fromHex matches: " . ($ulid_from_hex->toString() === $ulid_string ? "YES" : "NO") . "\n";

// Test 7: ULID-specific methods
echo "getTimestamp works: " . (is_int($ulid->getTimestamp()) ? "YES" : "NO") . "\n";
$randomness = $ulid->getRandomness();
echo "getRandomness length: " . strlen($randomness) . "\n";

// Test 8: Inherited Bit128 methods
echo "toHex works: " . (strlen($ulid->toHex()) === 32 ? "YES" : "NO") . "\n";
echo "toBytes works: " . (strlen($ulid->toBytes()) === 16 ? "YES" : "NO") . "\n";
echo "equals works: " . ($ulid->equals($ulid) ? "YES" : "NO") . "\n";

// Test 9: Fixed context provides deterministic randomness
$det_context1 = Fixed::create(1234567890000, 12345);
$det_context2 = Fixed::create(1234567890000, 12345); // Same parameters, fresh context
$random1 = $det_context1->getRandomBytes(10);
$random2 = $det_context2->getRandomBytes(10);
echo "Deterministic random bytes: " . ($random1 === $random2 ? "YES" : "NO") . "\n";

// Test 10: Error cases
try {
    Ulid::fromString("INVALID");
    echo "Invalid ULID error: FAIL\n";
} catch (Exception $e) {
    echo "Invalid ULID error: SUCCESS\n";
}

try {
    Ulid::fromBytes("short");
    echo "Invalid bytes error: FAIL\n";
} catch (Exception $e) {
    echo "Invalid bytes error: SUCCESS\n";
}

try {
    Ulid::fromHex("invalid");
    echo "Invalid hex error: FAIL\n";
} catch (Exception $e) {
    echo "Invalid hex error: SUCCESS\n";
}
?>
--EXPECT--
ULID generated: YES
ULID string length: 26
ULID string format: VALID
__toString works: YES
fromString creates ULID: YES
fromString matches: YES
fromBytes creates ULID: YES
fromBytes matches: YES
fromHex creates ULID: YES
fromHex matches: YES
getTimestamp works: YES
getRandomness length: 10
toHex works: YES
toBytes works: YES
equals works: YES
Deterministic random bytes: YES
Invalid ULID error: SUCCESS
Invalid bytes error: SUCCESS
Invalid hex error: SUCCESS
