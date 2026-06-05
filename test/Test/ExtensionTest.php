<?php

declare(strict_types=1);

namespace Identifier\Test;

use Identifier\Bit128;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class ExtensionTest extends TestCase
{
    #[Test]
    public function extension_is_loaded(): void
    {
        self::assertTrue(extension_loaded('identifier'));
    }

    #[Test]
    public function main_classes_are_available(): void
    {
        foreach ([
            Bit128::class,
            'Identifier\\Uuid',
            'Identifier\\Uuid\\Version1',
            'Identifier\\Uuid\\Version3',
            'Identifier\\Uuid\\Version4',
            'Identifier\\Uuid\\Version5',
            'Identifier\\Uuid\\Version6',
            'Identifier\\Uuid\\Version7',
            'Identifier\\Ulid',
            'Identifier\\State\\System',
            'Identifier\\State\\Fixed',
        ] as $class) {
            self::assertTrue(class_exists($class), sprintf('Class %s should exist', $class));
        }
    }

    #[Test]
    public function bit128_is_concrete_and_stringable(): void
    {
        $reflection = new ReflectionClass(Bit128::class);

        self::assertFalse($reflection->isAbstract());
        self::assertTrue($reflection->implementsInterface(\Stringable::class));
    }
}
