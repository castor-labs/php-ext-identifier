<?php

declare(strict_types=1);

namespace Identifier\Test;

use Exception;
use Identifier\Bit128;
use Identifier\Ulid;
use Identifier\Uuid;
use Identifier\Uuid\Version4;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

final class Bit128Test extends TestCase
{
    #[Test]
    public function it_wraps_exactly_16_bytes(): void
    {
        $bytes = hex2bin('0123456789abcdef0123456789abcdef');
        self::assertIsString($bytes);

        $bit128 = Bit128::fromBytes($bytes);

        self::assertSame($bytes, $bit128->getBytes());
        self::assertSame($bit128->getBytes(), $bit128->toBytes());
        self::assertSame('0123456789abcdef0123456789abcdef', $bit128->toHex());
        self::assertSame($bit128->toHex(), $bit128->toString());
        self::assertSame($bit128->toString(), (string) $bit128);
    }

    #[Test]
    public function it_can_be_created_from_bytes(): void
    {
        $bytes = hex2bin('aabbccddeeff00112233445566778899');
        self::assertIsString($bytes);
        $fromBytes = Bit128::fromBytes($bytes);
        self::assertSame('aabbccddeeff00112233445566778899', $fromBytes->toHex());
    }

    #[Test]
    public function static_factories_use_late_static_binding(): void
    {
        $uuidBytes = hex2bin('550e8400e29b41d4a716446655440000');
        $ulidBytes = hex2bin('0188bac7b8de4c4aaa5f8c3e0cd5e5e3');
        self::assertIsString($uuidBytes);
        self::assertIsString($ulidBytes);

        self::assertInstanceOf(Version4::class, Version4::fromBytes($uuidBytes));
        self::assertInstanceOf(Version4::class, Version4::fromHex('550e8400-e29b-41d4-a716-446655440000'));
        self::assertInstanceOf(Version4::class, Uuid::fromBytes($uuidBytes));
        self::assertInstanceOf(Ulid::class, Ulid::fromBytes($ulidBytes));
    }

    #[Test]
    public function constructor_is_protected_and_final(): void
    {
        $constructor = new ReflectionMethod(Bit128::class, '__construct');

        self::assertTrue($constructor->isProtected());
        self::assertTrue($constructor->isFinal());
    }

    #[Test]
    public function it_round_trips_bytes(): void
    {
        $hex = 'deadbeefcafebabe1234567890abcdef';
        $bytes = hex2bin($hex);
        self::assertIsString($bytes);

        self::assertSame($bytes, Bit128::fromBytes($bytes)->toBytes());
    }

    #[Test]
    public function it_compares_identifiers(): void
    {
        $a = Bit128::fromBytes(hex2bin('1234567890abcdef1234567890abcdef'));
        $b = Bit128::fromBytes(hex2bin('1234567890abcdef1234567890abcdef'));
        $c = Bit128::fromBytes(hex2bin('fedcba0987654321fedcba0987654321'));

        self::assertTrue($a->equals($b));
        self::assertFalse($a->equals($c));
        self::assertSame(0, $a->compare($b));
        self::assertNotSame(0, $a->compare($c));
    }

    #[Test]
    public function it_rejects_invalid_input(): void
    {
        $this->expectException(Exception::class);
        Bit128::fromBytes('short');
    }

    #[Test]
    public function it_rejects_invalid_hex(): void
    {
        $this->expectException(Exception::class);
        Bit128::fromHex('invalid');
    }
}
