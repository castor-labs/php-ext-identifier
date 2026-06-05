<?php

declare(strict_types=1);

namespace Identifier\Test;

use Exception;
use Identifier\State\Fixed;
use Identifier\Ulid;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

final class UlidTest extends TestCase
{
    #[Test]
    public function it_generates_valid_ulids(): void
    {
        $ulid = Ulid::generate();
        $string = $ulid->toString();

        self::assertInstanceOf(Ulid::class, $ulid);
        self::assertSame(26, strlen($string));
        self::assertMatchesRegularExpression('/^[0-9A-Z]{26}$/', $string);
        self::assertSame($string, (string) $ulid);
        self::assertSame('Identifier\\Bit128', (new ReflectionMethod($ulid, '__toString'))->getDeclaringClass()->getName());
        self::assertSame("ULID: $string", "ULID: $ulid");
    }

    #[Test]
    public function it_round_trips_from_string_bytes_and_hex(): void
    {
        $ulid = Ulid::generate();
        $string = $ulid->toString();

        self::assertSame($string, Ulid::fromString($string)->toString());
        self::assertSame($string, Ulid::fromBytes($ulid->toBytes())->toString());
        self::assertSame($string, Ulid::fromHex($ulid->toHex())->toString());
    }

    #[Test]
    public function it_exposes_timestamp_randomness_and_bit128_methods(): void
    {
        $ulid = Ulid::generate();

        self::assertIsInt($ulid->getTimestamp());
        self::assertSame(10, strlen($ulid->getRandomness()));
        self::assertSame(32, strlen($ulid->toHex()));
        self::assertSame(16, strlen($ulid->toBytes()));
        self::assertTrue($ulid->equals($ulid));
    }

    #[Test]
    public function fixed_state_randomness_is_deterministic(): void
    {
        $first = Fixed::create(1234567890000, 12345)->getRandomBytes(10);
        $second = Fixed::create(1234567890000, 12345)->getRandomBytes(10);

        self::assertSame($first, $second);
    }



    #[Test]
    public function it_rejects_invalid_ulid_strings(): void
    {
        $this->expectException(Exception::class);
        Ulid::fromString('INVALID');
    }

    #[Test]
    public function it_rejects_invalid_bytes(): void
    {
        $this->expectException(Exception::class);
        Ulid::fromBytes('short');
    }

    #[Test]
    public function it_rejects_invalid_hex(): void
    {
        $this->expectException(Exception::class);
        Ulid::fromHex('invalid');
    }

}
