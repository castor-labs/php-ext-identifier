<?php

/**
 * Quick performance comparison script
 * 
 * Simple timing-based benchmark for rapid development feedback
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Php\Identifier\Uuid\Version4 as ExtVersion4;
use Php\Identifier\Ulid as ExtUlid;
use Ramsey\Uuid\Uuid as RamseyUuid;
use Symfony\Component\Uid\Uuid as SymfonyUuid;
use Symfony\Component\Uid\Ulid as SymfonyUlid;

class QuickBench
{
    private int $iterations = 10000;
    
    public function run(): void
    {
        echo "🚀 Quick Performance Comparison\n";
        echo "Iterations: {$this->iterations}\n\n";
        
        $this->benchmarkUuidGeneration();
        $this->benchmarkUuidParsing();
        $this->benchmarkUlidGeneration();
        
        echo "\n✅ Benchmark complete!\n";
    }
    
    private function benchmarkUuidGeneration(): void
    {
        echo "📊 UUID v4 Generation Performance:\n";
        
        // Extension
        $start = microtime(true);
        for ($i = 0; $i < $this->iterations; $i++) {
            ExtVersion4::generate();
        }
        $extTime = microtime(true) - $start;
        
        // Ramsey
        $start = microtime(true);
        for ($i = 0; $i < $this->iterations; $i++) {
            RamseyUuid::uuid4();
        }
        $ramseyTime = microtime(true) - $start;
        
        // Symfony
        $start = microtime(true);
        for ($i = 0; $i < $this->iterations; $i++) {
            SymfonyUuid::v4();
        }
        $symfonyTime = microtime(true) - $start;
        
        $this->printResults([
            'Extension' => $extTime,
            'Ramsey' => $ramseyTime,
            'Symfony' => $symfonyTime,
        ]);
    }
    
    private function benchmarkUuidParsing(): void
    {
        echo "\n📊 UUID Parsing Performance:\n";
        
        $testUuid = 'f47ac10b-58cc-4372-a567-0e02b2c3d479';
        $iterations = $this->iterations / 10; // Fewer iterations for parsing
        
        // Extension
        $start = microtime(true);
        for ($i = 0; $i < $iterations; $i++) {
            ExtVersion4::fromString($testUuid);
        }
        $extTime = microtime(true) - $start;
        
        // Ramsey
        $start = microtime(true);
        for ($i = 0; $i < $iterations; $i++) {
            RamseyUuid::fromString($testUuid);
        }
        $ramseyTime = microtime(true) - $start;
        
        // Symfony
        $start = microtime(true);
        for ($i = 0; $i < $iterations; $i++) {
            SymfonyUuid::fromString($testUuid);
        }
        $symfonyTime = microtime(true) - $start;
        
        $this->printResults([
            'Extension' => $extTime,
            'Ramsey' => $ramseyTime,
            'Symfony' => $symfonyTime,
        ], $iterations);
    }
    
    private function benchmarkUlidGeneration(): void
    {
        echo "\n📊 ULID Generation Performance:\n";
        
        // Extension
        $start = microtime(true);
        for ($i = 0; $i < $this->iterations; $i++) {
            ExtUlid::generate();
        }
        $extTime = microtime(true) - $start;
        
        // Symfony
        $start = microtime(true);
        for ($i = 0; $i < $this->iterations; $i++) {
            SymfonyUlid::generate();
        }
        $symfonyTime = microtime(true) - $start;
        
        $this->printResults([
            'Extension' => $extTime,
            'Symfony' => $symfonyTime,
        ]);
    }
    
    private function printResults(array $results, ?int $iterations = null): void
    {
        $iterations = $iterations ?? $this->iterations;
        $fastest = min($results);
        
        foreach ($results as $name => $time) {
            $opsPerSec = number_format($iterations / $time, 0);
            $speedup = $fastest === $time ? '' : sprintf(' (%.1fx slower)', $time / $fastest);
            $isFastest = $fastest === $time ? ' 🏆' : '';
            
            printf("  %-12s: %8.4fs | %12s ops/sec%s%s\n", 
                $name, $time, $opsPerSec, $speedup, $isFastest);
        }
    }
}

// Check if extension is loaded
if (!extension_loaded('identifier')) {
    echo "❌ Error: identifier extension not loaded\n";
    echo "Run: zig build && php -d extension=./modules/identifier.so {$argv[0]}\n";
    exit(1);
}

$bench = new QuickBench();
$bench->run();
