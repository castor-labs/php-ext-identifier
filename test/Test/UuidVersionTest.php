<?php

declare(strict_types=1);

namespace Identifier\Test;

use Exception;
use Identifier\State\Fixed;
use Identifier\Uuid;
use Identifier\Uuid\Version1;
use Identifier\Uuid\Version3;
use Identifier\Uuid\Version4;
use Identifier\Uuid\Version5;
use Identifier\Uuid\Version6;
use Identifier\Uuid\Version7;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class UuidVersionTest extends TestCase
{
    #[Test]
    public function all_version_classes_generate_uuid_instances(): void
    {
        $namespace = Uuid::fromString('6ba7b810-9dad-11d1-80b4-00c04fd430c8');

        $uuids = [
            1 => Version1::generate(),
            3 => Version3::generate($namespace, 'example.com'),
            4 => Version4::generate(),
            5 => Version5::generate($namespace, 'example.com'),
            6 => Version6::generate(),
            7 => Version7::generate(),
        ];

        foreach ($uuids as $version => $uuid) {
            self::assertSame($version, $uuid->getVersion());
            self::assertSame(36, strlen($uuid->toString()));
            self::assertSame($uuid->toString(), (string) $uuid);
        }
    }

    #[Test]
    public function all_versions_round_trip_from_string_and_hex(): void
    {
        $namespace = Uuid::fromString('6ba7b810-9dad-11d1-80b4-00c04fd430c8');
        $uuids = [
            Version1::generate(),
            Version3::generate($namespace, 'example.com'),
            Version4::generate(),
            Version5::generate($namespace, 'example.com'),
            Version6::generate(),
            Version7::generate(),
        ];

        foreach ($uuids as $uuid) {
            $class = $uuid::class;
            self::assertInstanceOf($class, $class::fromString($uuid->toString()));
            self::assertInstanceOf($class, $class::fromHex(str_replace('-', '', $uuid->toString())));
        }
    }


    #[Test]
    public function fixed_state_generation_is_deterministic(): void
    {
        $first = Version4::generate(Fixed::create(1234567890000, 12345));
        $second = Version4::generate(Fixed::create(1234567890000, 12345));

        self::assertSame($first->toString(), $second->toString());
    }

    #[Test]
    public function hash_based_uuids_are_deterministic(): void
    {
        $namespace = Uuid::fromString('6ba7b810-9dad-11d1-80b4-00c04fd430c8');

        self::assertSame(
            Version3::generate($namespace, 'test')->toString(),
            Version3::generate($namespace, 'test')->toString(),
        );
        self::assertSame(
            Version5::generate($namespace, 'test')->toString(),
            Version5::generate($namespace, 'test')->toString(),
        );
    }
    #[Test]
    public function version_specific_string_parsers_validate_version(): void
    {
        $this->expectException(Exception::class);
        Version1::fromString(Version4::generate()->toString());
    }

}
