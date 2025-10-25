<?php

namespace Bench;

use PhpBench\Attributes as Bench;
use Php\Identifier\Uuid\Version4 as ExtVersion4;
use Php\Identifier\Uuid\Version1 as ExtVersion1;
use Php\Identifier\Uuid\Version7 as ExtVersion7;
use Ramsey\Uuid\Uuid as RamseyUuid;
use Symfony\Component\Uid\Uuid as SymfonyUuid;
use Symfony\Component\Uid\UuidV4 as SymfonyUuidV4;
use Symfony\Component\Uid\UuidV7 as SymfonyUuidV7;

/**
 * Benchmark UUID generation performance across different libraries
 */
#[Bench\BeforeMethods('setUp')]
class UuidGenerationBench
{
    public function setUp(): void
    {
        // Ensure extension is loaded
        if (!extension_loaded('identifier')) {
            throw new \RuntimeException('identifier extension not loaded');
        }
    }

    /**
     * Benchmark UUID v4 generation - Extension
     */
    #[Bench\Revs(1000)]
    #[Bench\Iterations(10)]
    #[Bench\Subject]
    public function benchExtensionV4Generation(): void
    {
        ExtVersion4::generate();
    }

    /**
     * Benchmark UUID v4 generation - Ramsey
     */
    #[Bench\Revs(1000)]
    #[Bench\Iterations(10)]
    #[Bench\Subject]
    public function benchRamseyV4Generation(): void
    {
        RamseyUuid::uuid4();
    }

    /**
     * Benchmark UUID v4 generation - Symfony
     */
    #[Bench\Revs(1000)]
    #[Bench\Iterations(10)]
    #[Bench\Subject]
    public function benchSymfonyV4Generation(): void
    {
        SymfonyUuidV4::v4();
    }

    /**
     * Benchmark UUID v1 generation - Extension
     */
    #[Bench\Revs(1000)]
    #[Bench\Iterations(10)]
    #[Bench\Subject]
    public function benchExtensionV1Generation(): void
    {
        ExtVersion1::generate();
    }

    /**
     * Benchmark UUID v1 generation - Ramsey
     */
    #[Bench\Revs(1000)]
    #[Bench\Iterations(10)]
    #[Bench\Subject]
    public function benchRamseyV1Generation(): void
    {
        RamseyUuid::uuid1();
    }

    /**
     * Benchmark UUID v1 generation - Symfony
     */
    #[Bench\Revs(1000)]
    #[Bench\Iterations(10)]
    #[Bench\Subject]
    public function benchSymfonyV1Generation(): void
    {
        SymfonyUuid::v1();
    }

    /**
     * Benchmark UUID v7 generation - Extension
     */
    #[Bench\Revs(1000)]
    #[Bench\Iterations(10)]
    #[Bench\Subject]
    public function benchExtensionV7Generation(): void
    {
        ExtVersion7::generate();
    }

    /**
     * Benchmark UUID v7 generation - Symfony
     */
    #[Bench\Revs(1000)]
    #[Bench\Iterations(10)]
    #[Bench\Subject]
    public function benchSymfonyV7Generation(): void
    {
        SymfonyUuidV7::v7();
    }

    /**
     * Benchmark native PHP uuid extension v4 (if available)
     */
    #[Bench\Revs(1000)]
    #[Bench\Iterations(10)]
    #[Bench\Subject]
    #[Bench\Skip(condition: '!function_exists("uuid_create")')]
    public function benchNativeUuidV4Generation(): void
    {
        if (defined('UUID_TYPE_RANDOM')) {
            uuid_create(UUID_TYPE_RANDOM);
        }
    }
}
