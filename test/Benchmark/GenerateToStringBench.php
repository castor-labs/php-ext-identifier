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
final class GenerateToStringBench
{
    #[Bench]
    public function extension_uuid_v4(): void
    {
        ExtensionUuidV4::generate()->toString();
    }

    #[Bench]
    public function symfony_uuid_v4(): void
    {
        SymfonyUuid::v4()->toRfc4122();
    }

    #[Bench]
    public function ramsey_uuid_v4(): void
    {
        RamseyUuid::uuid4()->toString();
    }

    #[Bench]
    public function extension_uuid_v7(): void
    {
        ExtensionUuidV7::generate()->toString();
    }

    #[Bench]
    public function symfony_uuid_v7(): void
    {
        SymfonyUuid::v7()->toRfc4122();
    }

    #[Bench]
    public function ramsey_uuid_v7(): void
    {
        RamseyUuid::uuid7()->toString();
    }

    #[Bench]
    public function extension_ulid(): void
    {
        ExtensionUlid::generate()->toString();
    }

    #[Bench]
    public function symfony_ulid(): void
    {
        (new SymfonyUlid())->toBase32();
    }
}
