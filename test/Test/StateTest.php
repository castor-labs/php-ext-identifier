<?php

declare(strict_types=1);

namespace Identifier\Test;

use Exception;
use Identifier\State\Fixed;
use Identifier\State\System;
use Identifier\Ulid;
use Identifier\Uuid\Version4;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class StateTest extends TestCase
{
    #[Test]
    public function system_and_fixed_states_can_be_created(): void
    {
        self::assertInstanceOf(System::class, new System());
        self::assertInstanceOf(Fixed::class, Fixed::create(1234567890000, 12345));
    }

    #[Test]
    public function fixed_state_exposes_and_advances_time(): void
    {
        $state = Fixed::create(1234567890000, 12345);

        self::assertSame(1234567890000, $state->getTimestampMs());
        self::assertSame(16, strlen($state->getRandomBytes(16)));

        $state->advanceTime(1000);
        self::assertSame(1234567891000, $state->getTimestampMs());
    }

    #[Test]
    public function fixed_state_randomness_depends_on_seed(): void
    {
        $first = Fixed::create(1000, 123)->getRandomBytes(8);
        $second = Fixed::create(1000, 123)->getRandomBytes(8);
        $third = Fixed::create(1000, 456)->getRandomBytes(8);

        self::assertSame($first, $second);
        self::assertNotSame($first, $third);
    }

    #[Test]
    public function states_can_be_used_for_generation(): void
    {
        $system = new System();
        $fixed = Fixed::create(1234567890000, 12345);

        self::assertInstanceOf(Version4::class, Version4::generate($system));
        self::assertInstanceOf(Version4::class, Version4::generate($fixed));
        self::assertInstanceOf(Ulid::class, Ulid::generate($system));
        self::assertInstanceOf(Ulid::class, Ulid::generate($fixed));
    }

    #[Test]
    public function default_state_generation_works(): void
    {
        self::assertInstanceOf(Version4::class, Version4::generate());
        self::assertInstanceOf(Ulid::class, Ulid::generate());
    }


    #[Test]
    public function fixed_state_rejects_zero_random_bytes_length(): void
    {
        $this->expectException(Exception::class);
        Fixed::create(1000, 123)->getRandomBytes(0);
    }

    #[Test]
    public function fixed_state_rejects_too_large_random_bytes_length(): void
    {
        $this->expectException(Exception::class);
        Fixed::create(1000, 123)->getRandomBytes(2000);
    }

}
