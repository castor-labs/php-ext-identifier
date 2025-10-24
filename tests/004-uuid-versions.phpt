--TEST--
UUID version classes basic functionality
--SKIPIF--
<?php if (!extension_loaded("identifier")) print "skip"; ?>
--FILE--
<?php
use Php\Identifier\Uuid\Version1;
use Php\Identifier\Uuid\Version3;
use Php\Identifier\Uuid\Version4;
use Php\Identifier\Uuid\Version5;
use Php\Identifier\Uuid\Version6;
use Php\Identifier\Uuid\Version7;
use Php\Identifier\Context\Fixed;

// Test 1: All version classes can generate UUIDs
$namespace_uuid = \Php\Identifier\Uuid::fromString('6ba7b810-9dad-11d1-80b4-00c04fd430c8');
$uuid1 = Version1::generate();
$uuid3 = Version3::generate($namespace_uuid, 'example.com');
$uuid4 = Version4::generate();
$uuid5 = Version5::generate($namespace_uuid, 'example.com');
$uuid6 = Version6::generate();
$uuid7 = Version7::generate();

echo "Version1 generated: " . ($uuid1 instanceof Version1 ? "YES" : "NO") . "\n";
echo "Version3 generated: " . ($uuid3 instanceof Version3 ? "YES" : "NO") . "\n";
echo "Version4 generated: " . ($uuid4 instanceof Version4 ? "YES" : "NO") . "\n";
echo "Version5 generated: " . ($uuid5 instanceof Version5 ? "YES" : "NO") . "\n";
echo "Version6 generated: " . ($uuid6 instanceof Version6 ? "YES" : "NO") . "\n";
echo "Version7 generated: " . ($uuid7 instanceof Version7 ? "YES" : "NO") . "\n";

// Test 2: All have correct versions
echo "Version1 version: " . $uuid1->getVersion() . "\n";
echo "Version3 version: " . $uuid3->getVersion() . "\n";
echo "Version4 version: " . $uuid4->getVersion() . "\n";
echo "Version5 version: " . $uuid5->getVersion() . "\n";
echo "Version6 version: " . $uuid6->getVersion() . "\n";
echo "Version7 version: " . $uuid7->getVersion() . "\n";

// Test 3: All can convert to string
echo "Version1 toString: " . (strlen($uuid1->toString()) === 36 ? "YES" : "NO") . "\n";
echo "Version3 toString: " . (strlen($uuid3->toString()) === 36 ? "YES" : "NO") . "\n";
echo "Version4 toString: " . (strlen($uuid4->toString()) === 36 ? "YES" : "NO") . "\n";
echo "Version5 toString: " . (strlen($uuid5->toString()) === 36 ? "YES" : "NO") . "\n";
echo "Version6 toString: " . (strlen($uuid6->toString()) === 36 ? "YES" : "NO") . "\n";
echo "Version7 toString: " . (strlen($uuid7->toString()) === 36 ? "YES" : "NO") . "\n";

// Test 4: fromString works for all versions
$uuid1_from_string = Version1::fromString($uuid1->toString());
$uuid3_from_string = Version3::fromString($uuid3->toString());
$uuid4_from_string = Version4::fromString($uuid4->toString());
$uuid5_from_string = Version5::fromString($uuid5->toString());
$uuid6_from_string = Version6::fromString($uuid6->toString());
$uuid7_from_string = Version7::fromString($uuid7->toString());

echo "Version1 fromString: " . ($uuid1_from_string instanceof Version1 ? "YES" : "NO") . "\n";
echo "Version3 fromString: " . ($uuid3_from_string instanceof Version3 ? "YES" : "NO") . "\n";
echo "Version4 fromString: " . ($uuid4_from_string instanceof Version4 ? "YES" : "NO") . "\n";
echo "Version5 fromString: " . ($uuid5_from_string instanceof Version5 ? "YES" : "NO") . "\n";
echo "Version6 fromString: " . ($uuid6_from_string instanceof Version6 ? "YES" : "NO") . "\n";
echo "Version7 fromString: " . ($uuid7_from_string instanceof Version7 ? "YES" : "NO") . "\n";

// Test 5: fromHex works for all versions
$uuid1_from_hex = Version1::fromHex(str_replace('-', '', $uuid1->toString()));
$uuid3_from_hex = Version3::fromHex(str_replace('-', '', $uuid3->toString()));
$uuid4_from_hex = Version4::fromHex(str_replace('-', '', $uuid4->toString()));
$uuid5_from_hex = Version5::fromHex(str_replace('-', '', $uuid5->toString()));
$uuid6_from_hex = Version6::fromHex(str_replace('-', '', $uuid6->toString()));
$uuid7_from_hex = Version7::fromHex(str_replace('-', '', $uuid7->toString()));

echo "Version1 fromHex: " . ($uuid1_from_hex instanceof Version1 ? "YES" : "NO") . "\n";
echo "Version3 fromHex: " . ($uuid3_from_hex instanceof Version3 ? "YES" : "NO") . "\n";
echo "Version4 fromHex: " . ($uuid4_from_hex instanceof Version4 ? "YES" : "NO") . "\n";
echo "Version5 fromHex: " . ($uuid5_from_hex instanceof Version5 ? "YES" : "NO") . "\n";
echo "Version6 fromHex: " . ($uuid6_from_hex instanceof Version6 ? "YES" : "NO") . "\n";
echo "Version7 fromHex: " . ($uuid7_from_hex instanceof Version7 ? "YES" : "NO") . "\n";

// Test 6: Version validation works (should throw exceptions)
try {
    Version1::fromHex(str_replace('-', '', $uuid4->toString())); // v4 hex to v1
    echo "Version validation: FAIL\n";
} catch (Exception $e) {
    echo "Version validation: SUCCESS\n";
}

// Test 7: Deterministic generation with Fixed context
$context = Fixed::create(1234567890000, 12345);
$det_uuid4_1 = Version4::generate($context);
$context2 = Fixed::create(1234567890000, 12345); // Same parameters
$det_uuid4_2 = Version4::generate($context2);
echo "Deterministic generation: " . ($det_uuid4_1->toString() === $det_uuid4_2->toString() ? "YES" : "NO") . "\n";

// Test 8: Hash-based UUIDs are deterministic
$hash_uuid3_1 = Version3::generate($namespace_uuid, 'test');
$hash_uuid3_2 = Version3::generate($namespace_uuid, 'test');
$hash_uuid5_1 = Version5::generate($namespace_uuid, 'test');
$hash_uuid5_2 = Version5::generate($namespace_uuid, 'test');
echo "Version3 deterministic: " . ($hash_uuid3_1->toString() === $hash_uuid3_2->toString() ? "YES" : "NO") . "\n";
echo "Version5 deterministic: " . ($hash_uuid5_1->toString() === $hash_uuid5_2->toString() ? "YES" : "NO") . "\n";
?>
--EXPECT--
Version1 generated: YES
Version3 generated: YES
Version4 generated: YES
Version5 generated: YES
Version6 generated: YES
Version7 generated: YES
Version1 version: 1
Version3 version: 3
Version4 version: 4
Version5 version: 5
Version6 version: 6
Version7 version: 7
Version1 toString: YES
Version3 toString: YES
Version4 toString: YES
Version5 toString: YES
Version6 toString: YES
Version7 toString: YES
Version1 fromString: YES
Version3 fromString: YES
Version4 fromString: YES
Version5 fromString: YES
Version6 fromString: YES
Version7 fromString: YES
Version1 fromHex: YES
Version3 fromHex: YES
Version4 fromHex: YES
Version5 fromHex: YES
Version6 fromHex: YES
Version7 fromHex: YES
Version validation: SUCCESS
Deterministic generation: YES
Version3 deterministic: YES
Version5 deterministic: YES
