<?php

declare(strict_types=1);

namespace Identifier\Test;

use Exception;
use Identifier\Uuid;
use Identifier\Uuid\Version1;
use Identifier\Uuid\Version3;
use Identifier\Uuid\Version4;
use Identifier\Uuid\Version5;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class UuidTest extends TestCase
{
    #[Test]
    public function it_parses_and_formats_uuid_strings(): void
    {
        $uuidString = '550e8400-e29b-41d4-a716-446655440000';
        $uuid = Uuid::fromString($uuidString);

        self::assertInstanceOf(Version4::class, $uuid);
        self::assertSame($uuidString, $uuid->toString());
        self::assertSame($uuidString, (string) $uuid);
        self::assertSame(4, $uuid->getVersion());
    }

    #[Test]
    public function it_detects_nil_uuid(): void
    {
        $nil = Uuid::nil();
        $regular = Uuid::fromString('550e8400-e29b-41d4-a716-446655440000');

        self::assertTrue($nil->isNil());
        self::assertFalse($regular->isNil());
    }

    #[Test]
    public function it_detects_max_uuid(): void
    {
        $max = Uuid::max();
        $regular = Uuid::fromString('550e8400-e29b-41d4-a716-446655440000');

        self::assertTrue($max->isMax());
        self::assertFalse($regular->isMax());
    }

    #[Test]
    public function it_detects_versions_from_hex_and_bytes(): void
    {
        self::assertInstanceOf(Version1::class, Uuid::fromHex('6ba7b810-9dad-11d1-80b4-00c04fd430c8'));
        self::assertInstanceOf(Version3::class, Uuid::fromHex('6ba7b811-9dad-31d1-80b4-00c04fd430c8'));
        self::assertInstanceOf(Version4::class, Uuid::fromHex('550e8400-e29b-41d4-a716-446655440000'));
        self::assertInstanceOf(Version5::class, Uuid::fromHex('6ba7b813-9dad-51d1-80b4-00c04fd430c8'));

        $bytes = hex2bin(str_replace('-', '', '550e8400-e29b-41d4-a716-446655440000'));
        self::assertIsString($bytes);
        self::assertInstanceOf(Version4::class, Uuid::fromBytes($bytes));
    }

    #[Test]
    public function it_inherits_bit128_methods(): void
    {
        $uuid = Uuid::fromString('550e8400-e29b-41d4-a716-446655440000');

        self::assertSame(32, strlen($uuid->toHex()));
        self::assertSame(16, strlen($uuid->toBytes()));
        self::assertTrue($uuid->equals($uuid));
    }

    #[Test]
    public function it_round_trips_on_bytes(): void
    {
        $a = Uuid::nil();
        $b = Uuid::fromBytes($a->toBytes());

        self::assertTrue($a->equals($b));
    }

    #[Test]
    public function it_rejects_invalid_uuid_strings(): void
    {
        $this->expectException(Exception::class);
        Uuid::fromString('invalid-uuid');
    }

    #[Test]
    public function it_rejects_invalid_uuid_bytes(): void
    {
        $this->expectException(Exception::class);
        Uuid::fromBytes('short');
    }
}
