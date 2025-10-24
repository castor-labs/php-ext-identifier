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
    'Php\\Identifier\\Bit128',
    'Php\\Identifier\\Uuid',
    'Php\\Identifier\\Uuid\\Version1',
    'Php\\Identifier\\Uuid\\Version3',
    'Php\\Identifier\\Uuid\\Version4',
    'Php\\Identifier\\Uuid\\Version5',
    'Php\\Identifier\\Uuid\\Version6',
    'Php\\Identifier\\Uuid\\Version7',
    'Php\\Identifier\\Ulid',
    'Php\\Identifier\\Context\\System',
    'Php\\Identifier\\Context\\Fixed',

];

foreach ($classes as $class) {
    echo "Class $class: " . (class_exists($class) ? "EXISTS" : "MISSING") . "\n";
}

// Test Bit128 is now concrete (not abstract)
$reflection = new ReflectionClass('Php\\Identifier\\Bit128');
echo "Bit128 is concrete: " . ($reflection->isAbstract() ? "NO" : "YES") . "\n";


?>
--EXPECT--
Extension loaded: YES
Class Php\Identifier\Bit128: EXISTS
Class Php\Identifier\Uuid: EXISTS
Class Php\Identifier\Uuid\Version1: EXISTS
Class Php\Identifier\Uuid\Version3: EXISTS
Class Php\Identifier\Uuid\Version4: EXISTS
Class Php\Identifier\Uuid\Version5: EXISTS
Class Php\Identifier\Uuid\Version6: EXISTS
Class Php\Identifier\Uuid\Version7: EXISTS
Class Php\Identifier\Ulid: EXISTS
Class Php\Identifier\Context\System: EXISTS
Class Php\Identifier\Context\Fixed: EXISTS
Bit128 is concrete: YES
