<?php

namespace Bench;

use PhpBench\Attributes as Bench;
use Php\Identifier\Uuid\Version4 as ExtVersion4;
use Ramsey\Uuid\Uuid as RamseyUuid;
use Symfony\Component\Uid\Uuid as SymfonyUuid;

/**
 * Benchmark UUID parsing performance across different libraries
 */
#[Bench\BeforeMethods('setUp')]
class UuidParsingBench
{
    private array $testUuids = [];

    public function setUp(): void
    {
        // Ensure extension is loaded
        if (!extension_loaded('identifier')) {
            throw new \RuntimeException('identifier extension not loaded');
        }

        // Generate test UUIDs for parsing
        $this->testUuids = [
            'f47ac10b-58cc-4372-a567-0e02b2c3d479',
            '6ba7b810-9dad-11d1-80b4-00c04fd430c8',
            '550e8400-e29b-41d4-a716-446655440000',
            '12345678-1234-5678-1234-567812345678',
            'a0eebc99-9c0b-4ef8-bb6d-6bb9bd380a11',
        ];
    }

    /**
     * Benchmark UUID parsing - Extension fromString
     */
    #[Bench\Revs(1000)]
    #[Bench\Iterations(10)]
    #[Bench\Subject]
    public function benchExtensionFromString(): void
    {
        foreach ($this->testUuids as $uuid) {
            ExtVersion4::fromString($uuid);
        }
    }

    /**
     * Benchmark UUID parsing - Extension fromHex
     */
    #[Bench\Revs(1000)]
    #[Bench\Iterations(10)]
    #[Bench\Subject]
    public function benchExtensionFromHex(): void
    {
        foreach ($this->testUuids as $uuid) {
            $hex = str_replace('-', '', $uuid);
            ExtVersion4::fromHex($hex);
        }
    }

    /**
     * Benchmark UUID parsing - Ramsey
     */
    #[Bench\Revs(1000)]
    #[Bench\Iterations(10)]
    #[Bench\Subject]
    public function benchRamseyFromString(): void
    {
        foreach ($this->testUuids as $uuid) {
            RamseyUuid::fromString($uuid);
        }
    }

    /**
     * Benchmark UUID parsing - Symfony
     */
    #[Bench\Revs(1000)]
    #[Bench\Iterations(10)]
    #[Bench\Subject]
    public function benchSymfonyFromString(): void
    {
        foreach ($this->testUuids as $uuid) {
            SymfonyUuid::fromString($uuid);
        }
    }

    /**
     * Benchmark UUID validation - Extension
     */
    #[Bench\Revs(1000)]
    #[Bench\Iterations(10)]
    #[Bench\Subject]
    public function benchExtensionValidation(): void
    {
        foreach ($this->testUuids as $uuid) {
            try {
                ExtVersion4::fromString($uuid);
            } catch (\Exception $e) {
                // Invalid UUID
            }
        }
    }

    /**
     * Benchmark UUID validation - Ramsey
     */
    #[Bench\Revs(1000)]
    #[Bench\Iterations(10)]
    #[Bench\Subject]
    public function benchRamseyValidation(): void
    {
        foreach ($this->testUuids as $uuid) {
            RamseyUuid::isValid($uuid);
        }
    }

    /**
     * Benchmark UUID validation - Symfony
     */
    #[Bench\Revs(1000)]
    #[Bench\Iterations(10)]
    #[Bench\Subject]
    public function benchSymfonyValidation(): void
    {
        foreach ($this->testUuids as $uuid) {
            SymfonyUuid::isValid($uuid);
        }
    }
}
