--TEST--
UUID base class functionality
--SKIPIF--
<?php if (!extension_loaded("identifier")) print "skip"; ?>
--FILE--
<?php
use Php\Identifier\Uuid;

// Test 1: fromString method with various formats
$uuid_string = '550e8400-e29b-41d4-a716-446655440000';
$uuid = Uuid::fromString($uuid_string);
echo "fromString creates UUID: " . ($uuid instanceof Uuid ? "YES" : "NO") . "\n";
echo "fromString class: " . get_class($uuid) . "\n";

// Test 2: toString method
$result_string = $uuid->toString();
echo "toString result: " . $result_string . "\n";
echo "toString matches: " . ($result_string === $uuid_string ? "YES" : "NO") . "\n";

// Test 3: __toString magic method
$magic_string = (string) $uuid;
echo "__toString works: " . ($magic_string === $uuid_string ? "YES" : "NO") . "\n";

// Test 4: getVersion method
echo "UUID version: " . $uuid->getVersion() . "\n";

// Test 5: isNil method
$nil_uuid = Uuid::fromString('00000000-0000-0000-0000-000000000000');
echo "Nil UUID detected: " . ($nil_uuid->isNil() ? "YES" : "NO") . "\n";
echo "Regular UUID is nil: " . ($uuid->isNil() ? "NO" : "YES") . "\n";

// Test 6: fromHex with smart version detection
$version1_hex = '6ba7b810-9dad-11d1-80b4-00c04fd430c8';
$version3_hex = '6ba7b811-9dad-31d1-80b4-00c04fd430c8';
$version4_hex = '550e8400-e29b-41d4-a716-446655440000';
$version5_hex = '6ba7b813-9dad-51d1-80b4-00c04fd430c8';

$uuid1 = Uuid::fromHex($version1_hex);
$uuid3 = Uuid::fromHex($version3_hex);
$uuid4 = Uuid::fromHex($version4_hex);
$uuid5 = Uuid::fromHex($version5_hex);

echo "Version 1 detection: " . get_class($uuid1) . "\n";
echo "Version 3 detection: " . get_class($uuid3) . "\n";
echo "Version 4 detection: " . get_class($uuid4) . "\n";
echo "Version 5 detection: " . get_class($uuid5) . "\n";

// Test 7: fromBytes with smart version detection
$uuid4_bytes = hex2bin(str_replace('-', '', $version4_hex));
$uuid_from_bytes = Uuid::fromBytes($uuid4_bytes);
echo "fromBytes detection: " . get_class($uuid_from_bytes) . "\n";

// Test 8: Inherited Bit128 methods
echo "toHex works: " . (strlen($uuid->toHex()) === 32 ? "YES" : "NO") . "\n";
echo "toBytes works: " . (strlen($uuid->toBytes()) === 16 ? "YES" : "NO") . "\n";
echo "equals works: " . ($uuid->equals($uuid) ? "YES" : "NO") . "\n";

// Test 9: Error cases
try {
    Uuid::fromString("invalid-uuid");
    echo "Invalid UUID error: FAIL\n";
} catch (Exception $e) {
    echo "Invalid UUID error: SUCCESS\n";
}

try {
    Uuid::fromBytes("short");
    echo "Invalid bytes error: FAIL\n";
} catch (Exception $e) {
    echo "Invalid bytes error: SUCCESS\n";
}
?>
--EXPECT--
fromString creates UUID: YES
fromString class: Php\Identifier\Uuid\Version4
toString result: 550e8400-e29b-41d4-a716-446655440000
toString matches: YES
__toString works: YES
UUID version: 4
Nil UUID detected: YES
Regular UUID is nil: YES
Version 1 detection: Php\Identifier\Uuid\Version1
Version 3 detection: Php\Identifier\Uuid\Version3
Version 4 detection: Php\Identifier\Uuid\Version4
Version 5 detection: Php\Identifier\Uuid\Version5
fromBytes detection: Php\Identifier\Uuid\Version4
toHex works: YES
toBytes works: YES
equals works: YES
Invalid UUID error: SUCCESS
Invalid bytes error: SUCCESS
