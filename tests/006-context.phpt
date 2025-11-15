--TEST--
Context classes functionality
--SKIPIF--
<?php if (!extension_loaded("identifier")) print "skip"; ?>
--FILE--
<?php
use Identifier\Context\System;
use Identifier\Context\Fixed;
use Identifier\Uuid\Version4;
use Identifier\Ulid;

// Test 1: System context
$system_context = new System();
echo "System context created: " . ($system_context instanceof System ? "YES" : "NO") . "\n";

// Test 2: Fixed context creation
$fixed_context = Fixed::create(1234567890000, 12345);
echo "Fixed context created: " . ($fixed_context instanceof Fixed ? "YES" : "NO") . "\n";

// Test 3: Fixed context methods
echo "getTimestampMs: " . ($fixed_context->getTimestampMs() === 1234567890000 ? "SUCCESS" : "FAIL") . "\n";

$random_bytes = $fixed_context->getRandomBytes(16);
echo "getRandomBytes: " . (strlen($random_bytes) === 16 ? "SUCCESS" : "FAIL") . "\n";

// Test 4: Advance time
$fixed_context->advanceTime(1000);
echo "advanceTime: " . ($fixed_context->getTimestampMs() === 1234567891000 ? "SUCCESS" : "FAIL") . "\n";

// Test 5: Deterministic random (same seed produces same result)
$context1 = Fixed::create(1000, 123);
$context2 = Fixed::create(1000, 123);
$random1 = $context1->getRandomBytes(8);
$random2 = $context2->getRandomBytes(8);
echo "Deterministic random: " . ($random1 === $random2 ? "SUCCESS" : "FAIL") . "\n";

// Test 6: Different seeds produce different results
$context3 = Fixed::create(1000, 456);
$random3 = $context3->getRandomBytes(8);
echo "Different seeds: " . ($random1 !== $random3 ? "SUCCESS" : "FAIL") . "\n";

// Test 7: Use contexts with UUID generation
$uuid_system = Version4::generate($system_context);
$uuid_fixed = Version4::generate($fixed_context);
echo "System context UUID: " . ($uuid_system instanceof Version4 ? "SUCCESS" : "FAIL") . "\n";
echo "Fixed context UUID: " . ($uuid_fixed instanceof Version4 ? "SUCCESS" : "FAIL") . "\n";

// Test 8: Use contexts with ULID generation
$ulid_system = Ulid::generate($system_context);
$ulid_fixed = Ulid::generate($fixed_context);
echo "System context ULID: " . ($ulid_system instanceof Ulid ? "SUCCESS" : "FAIL") . "\n";
echo "Fixed context ULID: " . ($ulid_fixed instanceof Ulid ? "SUCCESS" : "FAIL") . "\n";

// Test 9: Default context (no parameter) works
$uuid_default = Version4::generate();
$ulid_default = Ulid::generate();
echo "Default UUID: " . ($uuid_default instanceof Version4 ? "SUCCESS" : "FAIL") . "\n";
echo "Default ULID: " . ($ulid_default instanceof Ulid ? "SUCCESS" : "FAIL") . "\n";

// Test 10: Error cases
try {
    $error_context = Fixed::create(1000, 123);
    $error_context->getRandomBytes(0); // Invalid length
    echo "Invalid random bytes length: FAIL\n";
} catch (Exception $e) {
    echo "Invalid random bytes length: SUCCESS\n";
}

try {
    $error_context = Fixed::create(1000, 123);
    $error_context->getRandomBytes(2000); // Too large
    echo "Random bytes too large: FAIL\n";
} catch (Exception $e) {
    echo "Random bytes too large: SUCCESS\n";
}
?>
--EXPECT--
System context created: YES
Fixed context created: YES
getTimestampMs: SUCCESS
getRandomBytes: SUCCESS
advanceTime: SUCCESS
Deterministic random: SUCCESS
Different seeds: SUCCESS
System context UUID: SUCCESS
Fixed context UUID: SUCCESS
System context ULID: SUCCESS
Fixed context ULID: SUCCESS
Default UUID: SUCCESS
Default ULID: SUCCESS
Invalid random bytes length: SUCCESS
Random bytes too large: SUCCESS
