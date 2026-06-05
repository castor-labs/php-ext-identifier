<?php

declare(strict_types=1);

namespace Identifier\Benchmark;

use Identifier\Ulid as ExtensionUlid;
use Identifier\Uuid\Version4 as ExtensionUuidV4;
use Identifier\Uuid\Version7 as ExtensionUuidV7;
use PhpBench\Attributes as PhpBench;
use PhpBench\Attributes\Subject as Bench;
use Ramsey\Uuid\Uuid as RamseyUuid;
use Symfony\Component\Uid\Ulid as SymfonyUlid;
use Symfony\Component\Uid\Uuid as SymfonyUuid;

#[PhpBench\Revs(1000)]
#[PhpBench\Iterations(5)]
#[PhpBench\Warmup(2)]
#[PhpBench\OutputMode('throughput')]
#[PhpBench\OutputTimeUnit('seconds')]
final class ParseToBytesBench
{
    private const UUID_V4 = '550e8400-e29b-41d4-a716-446655440000';
    private const UUID_V7 = '018c2e65-4b0a-7c3d-8f2e-1a4b5c6d7e8f';
    private const ULID = '01ARZ3NDEKTSV4RRFFQ69G5FAV';

    #[Bench]
    public function extension_uuid_v4(): void
    {
        ExtensionUuidV4::fromString(self::UUID_V4)->toBytes();
    }

    #[Bench]
    public function symfony_uuid_v4(): void
    {
        SymfonyUuid::fromString(self::UUID_V4)->toBinary();
    }

    #[Bench]
    public function ramsey_uuid_v4(): void
    {
        RamseyUuid::fromString(self::UUID_V4)->getBytes();
    }

    #[Bench]
    public function extension_uuid_v7(): void
    {
        ExtensionUuidV7::fromString(self::UUID_V7)->toBytes();
    }

    #[Bench]
    public function symfony_uuid_v7(): void
    {
        SymfonyUuid::fromString(self::UUID_V7)->toBinary();
    }

    #[Bench]
    public function ramsey_uuid_v7(): void
    {
        RamseyUuid::fromString(self::UUID_V7)->getBytes();
    }

    #[Bench]
    public function extension_ulid(): void
    {
        ExtensionUlid::fromString(self::ULID)->toBytes();
    }

    #[Bench]
    public function symfony_ulid(): void
    {
        SymfonyUlid::fromString(self::ULID)->toBinary();
    }
}
