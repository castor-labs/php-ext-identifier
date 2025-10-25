<?php

/**
 * Standalone benchmark script - no external dependencies
 * 
 * Tests only the identifier extension performance without comparison libraries
 */

// Check if extension is loaded
if (!extension_loaded('identifier')) {
    echo "❌ Error: identifier extension not loaded\n";
    echo "Run: zig build && php -d extension=./modules/identifier.so {$argv[0]}\n";
    exit(1);
}

use Php\Identifier\Uuid\Version4;
use Php\Identifier\Uuid\Version1;
use Php\Identifier\Uuid\Version7;
use Php\Identifier\Ulid;

class StandaloneBench
{
    private int $iterations = 10000;
    
    public function run(): void
    {
        echo "🚀 PHP Identifier Extension Performance Test\n";
        echo "Iterations: {$this->iterations}\n\n";
        
        $this->benchmarkUuidGeneration();
        $this->benchmarkUuidParsing();
        $this->benchmarkUlidOperations();
        
        echo "\n✅ Benchmark complete!\n";
        echo "\nNote: Install ramsey/uuid and symfony/uid in bench/ directory for comparison benchmarks.\n";
    }
    
    private function benchmarkUuidGeneration(): void
    {
        echo "📊 UUID Generation Performance:\n";
        
        // UUID v4
        $start = microtime(true);
        for ($i = 0; $i < $this->iterations; $i++) {
            Version4::generate();
        }
        $v4Time = microtime(true) - $start;
        
        // UUID v1
        $start = microtime(true);
        for ($i = 0; $i < $this->iterations; $i++) {
            Version1::generate();
        }
        $v1Time = microtime(true) - $start;
        
        // UUID v7
        $start = microtime(true);
        for ($i = 0; $i < $this->iterations; $i++) {
            Version7::generate();
        }
        $v7Time = microtime(true) - $start;
        
        $this->printResults([
            'UUID v4' => $v4Time,
            'UUID v1' => $v1Time,
            'UUID v7' => $v7Time,
        ]);
    }
    
    private function benchmarkUuidParsing(): void
    {
        echo "\n📊 UUID Parsing Performance:\n";

        // Generate actual v4 UUIDs for testing
        $testUuids = [];
        for ($i = 0; $i < 5; $i++) {
            $testUuids[] = Version4::generate()->toString();
        }

        $iterations = $this->iterations / 10; // Fewer iterations for parsing

        // fromString
        $start = microtime(true);
        for ($i = 0; $i < $iterations; $i++) {
            foreach ($testUuids as $uuid) {
                Version4::fromString($uuid);
            }
        }
        $fromStringTime = microtime(true) - $start;

        // fromHex
        $start = microtime(true);
        for ($i = 0; $i < $iterations; $i++) {
            foreach ($testUuids as $uuid) {
                $hex = str_replace('-', '', $uuid);
                Version4::fromHex($hex);
            }
        }
        $fromHexTime = microtime(true) - $start;

        $this->printResults([
            'fromString' => $fromStringTime,
            'fromHex' => $fromHexTime,
        ], $iterations * count($testUuids));
    }
    
    private function benchmarkUlidOperations(): void
    {
        echo "\n📊 ULID Performance:\n";
        
        // Generation
        $start = microtime(true);
        for ($i = 0; $i < $this->iterations; $i++) {
            Ulid::generate();
        }
        $generateTime = microtime(true) - $start;
        
        // Parsing - generate real ULIDs for testing
        $testUlids = [];
        for ($i = 0; $i < 3; $i++) {
            $testUlids[] = Ulid::generate()->toString();
        }

        $iterations = $this->iterations / 10;
        $start = microtime(true);
        for ($i = 0; $i < $iterations; $i++) {
            foreach ($testUlids as $ulid) {
                Ulid::fromString($ulid);
            }
        }
        $parseTime = microtime(true) - $start;
        
        // Timestamp extraction
        $ulid = Ulid::generate();
        $start = microtime(true);
        for ($i = 0; $i < $this->iterations; $i++) {
            $ulid->getTimestamp();
        }
        $timestampTime = microtime(true) - $start;
        
        $this->printResults([
            'Generation' => $generateTime,
            'Parsing' => $parseTime,
            'Timestamp' => $timestampTime,
        ], $this->iterations);
    }
    
    private function printResults(array $results, ?int $iterations = null): void
    {
        $iterations = $iterations ?? $this->iterations;
        $fastest = min($results);
        
        foreach ($results as $name => $time) {
            $opsPerSec = number_format($iterations / $time, 0);
            $speedup = $fastest === $time ? '' : sprintf(' (%.1fx slower)', $time / $fastest);
            $isFastest = $fastest === $time ? ' 🏆' : '';
            
            printf("  %-15s: %8.4fs | %12s ops/sec%s%s\n", 
                $name, $time, $opsPerSec, $speedup, $isFastest);
        }
    }
}

$bench = new StandaloneBench();
$bench->run();
