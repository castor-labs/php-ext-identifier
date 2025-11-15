--TEST--
Extension loading and basic class availability
--SKIPIF--
<?php if (!extension_loaded("identifier")) print "skip"; ?>
--FILE--
<?php
// Test extension is loaded
echo "Extension loaded: " . (extension_loaded("identifier") ? "YES" : "NO") . "\n";

// Test all main classes exist
$classes = [
    'Identifier\\Bit128',
    'Identifier\\Uuid',
    'Identifier\\Uuid\\Version1',
    'Identifier\\Uuid\\Version3',
    'Identifier\\Uuid\\Version4',
    'Identifier\\Uuid\\Version5',
    'Identifier\\Uuid\\Version6',
    'Identifier\\Uuid\\Version7',
    'Identifier\\Ulid',
    'Identifier\\Context\\System',
    'Identifier\\Context\\Fixed',

];

foreach ($classes as $class) {
    echo "Class $class: " . (class_exists($class) ? "EXISTS" : "MISSING") . "\n";
}

// Test Bit128 is now concrete (not abstract)
$reflection = new ReflectionClass('Identifier\\Bit128');
echo "Bit128 is concrete: " . ($reflection->isAbstract() ? "NO" : "YES") . "\n";


?>
--EXPECT--
Extension loaded: YES
Class Identifier\Bit128: EXISTS
Class Identifier\Uuid: EXISTS
Class Identifier\Uuid\Version1: EXISTS
Class Identifier\Uuid\Version3: EXISTS
Class Identifier\Uuid\Version4: EXISTS
Class Identifier\Uuid\Version5: EXISTS
Class Identifier\Uuid\Version6: EXISTS
Class Identifier\Uuid\Version7: EXISTS
Class Identifier\Ulid: EXISTS
Class Identifier\Context\System: EXISTS
Class Identifier\Context\Fixed: EXISTS
Bit128 is concrete: YES
