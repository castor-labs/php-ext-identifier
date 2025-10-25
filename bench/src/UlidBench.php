<?php

namespace Bench;

use PhpBench\Attributes as Bench;
use Php\Identifier\Ulid as ExtUlid;
use Symfony\Component\Uid\Ulid as SymfonyUlid;

/**
 * Benchmark ULID performance across different libraries
 */
#[Bench\BeforeMethods('setUp')]
class UlidBench
{
    private array $testUlids = [];

    public function setUp(): void
    {
        // Ensure extension is loaded
        if (!extension_loaded('identifier')) {
            throw new \RuntimeException('identifier extension not loaded');
        }

        // Generate test ULIDs for parsing
        $this->testUlids = [
            '01ARZ3NDEKTSV4RRFFQ69G5FAV',
            '01BX5ZZKBKACTAV9WEVGEMMVRZ',
            '01CXYZ123456789ABCDEFGHIJK',
            '01DXYZ987654321ZYXWVUTSRQP',
            '01EXYZ456789012MNOPQRSTUVW',
        ];
    }

    /**
     * Benchmark ULID generation - Extension
     */
    #[Bench\Revs(1000)]
    #[Bench\Iterations(10)]
    #[Bench\Subject]
    public function benchExtensionGeneration(): void
    {
        ExtUlid::generate();
    }

    /**
     * Benchmark ULID generation - Symfony
     */
    #[Bench\Revs(1000)]
    #[Bench\Iterations(10)]
    #[Bench\Subject]
    public function benchSymfonyGeneration(): void
    {
        SymfonyUlid::generate();
    }

    /**
     * Benchmark ULID parsing - Extension
     */
    #[Bench\Revs(1000)]
    #[Bench\Iterations(10)]
    #[Bench\Subject]
    public function benchExtensionFromString(): void
    {
        foreach ($this->testUlids as $ulid) {
            ExtUlid::fromString($ulid);
        }
    }

    /**
     * Benchmark ULID parsing - Symfony
     */
    #[Bench\Revs(1000)]
    #[Bench\Iterations(10)]
    #[Bench\Subject]
    public function benchSymfonyFromString(): void
    {
        foreach ($this->testUlids as $ulid) {
            SymfonyUlid::fromString($ulid);
        }
    }

    /**
     * Benchmark ULID timestamp extraction - Extension
     */
    #[Bench\Revs(1000)]
    #[Bench\Iterations(10)]
    #[Bench\Subject]
    public function benchExtensionTimestamp(): void
    {
        $ulid = ExtUlid::generate();
        $ulid->getTimestamp();
    }

    /**
     * Benchmark ULID timestamp extraction - Symfony
     */
    #[Bench\Revs(1000)]
    #[Bench\Iterations(10)]
    #[Bench\Subject]
    public function benchSymfonyTimestamp(): void
    {
        $ulid = SymfonyUlid::generate();
        $ulid->getDateTime();
    }

    /**
     * Benchmark ULID validation - Extension
     */
    #[Bench\Revs(1000)]
    #[Bench\Iterations(10)]
    #[Bench\Subject]
    public function benchExtensionValidation(): void
    {
        foreach ($this->testUlids as $ulid) {
            try {
                ExtUlid::fromString($ulid);
            } catch (\Exception $e) {
                // Invalid ULID
            }
        }
    }

    /**
     * Benchmark ULID validation - Symfony
     */
    #[Bench\Revs(1000)]
    #[Bench\Iterations(10)]
    #[Bench\Subject]
    public function benchSymfonyValidation(): void
    {
        foreach ($this->testUlids as $ulid) {
            SymfonyUlid::isValid($ulid);
        }
    }
}
