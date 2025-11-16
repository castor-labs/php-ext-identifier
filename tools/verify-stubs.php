#!/usr/bin/env php
<?php
/**
 * PHP Stub Verification Tool
 * 
 * Compares manual stubs with generated stubs to ensure they match the actual API.
 * This helps catch when manual stubs get out of sync with the implementation.
 */

function parseStubFile(string $filename): array {
    if (!file_exists($filename)) {
        throw new RuntimeException("Stub file not found: $filename");
    }
    
    $content = file_get_contents($filename);
    $classes = [];
    
    // Simple regex-based parsing to extract class signatures
    // This is not a full PHP parser, but good enough for stub comparison
    
    // Extract classes
    if (preg_match_all('/(?:abstract\s+|final\s+)?class\s+(\w+)(?:\s+extends\s+([\\\\]?\w+(?:\\\\[\w\\\\]+)?))?(?:\s+implements\s+([^{]+))?/i', $content, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $match) {
            $className = $match[1];
            $extends = isset($match[2]) ? trim($match[2]) : null;
            $implements = isset($match[3]) ? array_map('trim', explode(',', $match[3])) : [];
            
            $classes[$className] = [
                'extends' => $extends,
                'implements' => $implements,
                'methods' => []
            ];
        }
    }
    
    // Extract methods for each class
    foreach ($classes as $className => &$classInfo) {
        $pattern = '/class\s+' . preg_quote($className) . '.*?\{(.*?)(?=class\s+\w+|\z)/s';
        if (preg_match($pattern, $content, $classMatch)) {
            $classContent = $classMatch[1];
            
            // Extract method signatures
            if (preg_match_all('/(?:public|protected|private)(?:\s+static)?\s+function\s+(\w+)\s*\([^)]*\)(?:\s*:\s*[^{]+)?/i', $classContent, $methodMatches, PREG_SET_ORDER)) {
                foreach ($methodMatches as $methodMatch) {
                    $methodName = $methodMatch[1];
                    $fullSignature = trim($methodMatch[0]);
                    $classInfo['methods'][$methodName] = $fullSignature;
                }
            }
        }
    }
    
    return $classes;
}

function compareStubs(array $manual, array $generated): array {
    $issues = [];
    
    // Check for missing classes in manual stubs
    foreach ($generated as $className => $generatedClass) {
        if (!isset($manual[$className])) {
            $issues[] = "❌ Missing class in manual stubs: $className";
            continue;
        }
        
        $manualClass = $manual[$className];
        
        // Check inheritance
        if ($generatedClass['extends'] !== $manualClass['extends']) {
            $issues[] = "❌ Class $className inheritance mismatch:";
            $issues[] = "   Manual: " . ($manualClass['extends'] ?: 'none');
            $issues[] = "   Generated: " . ($generatedClass['extends'] ?: 'none');
        }
        
        // Check for missing methods in manual stubs
        foreach ($generatedClass['methods'] as $methodName => $generatedSignature) {
            if (!isset($manualClass['methods'][$methodName])) {
                $issues[] = "❌ Missing method in manual stubs: $className::$methodName";
                $issues[] = "   Expected: $generatedSignature";
            }
        }
        
        // Check for extra methods in manual stubs (might be okay, but worth noting)
        foreach ($manualClass['methods'] as $methodName => $manualSignature) {
            if (!isset($generatedClass['methods'][$methodName])) {
                $issues[] = "⚠️  Extra method in manual stubs: $className::$methodName";
                $issues[] = "   Manual: $manualSignature";
            }
        }
    }
    
    // Check for extra classes in manual stubs
    foreach ($manual as $className => $manualClass) {
        if (!isset($generated[$className])) {
            $issues[] = "⚠️  Extra class in manual stubs: $className";
        }
    }
    
    return $issues;
}

function generateReport(array $manual, array $generated, array $issues): string {
    $report = "# Stub Verification Report\n\n";
    $report .= "Generated: " . date('Y-m-d H:i:s') . "\n\n";
    
    $report .= "## Summary\n\n";
    $report .= "- Manual stubs classes: " . count($manual) . "\n";
    $report .= "- Generated stubs classes: " . count($generated) . "\n";
    $report .= "- Issues found: " . count($issues) . "\n\n";
    
    if (empty($issues)) {
        $report .= "✅ **All checks passed!** Manual stubs are in sync with the extension API.\n\n";
    } else {
        $report .= "## Issues Found\n\n";
        foreach ($issues as $issue) {
            $report .= "$issue\n";
        }
        $report .= "\n";
    }
    
    $report .= "## Class Comparison\n\n";
    $allClasses = array_unique(array_merge(array_keys($manual), array_keys($generated)));
    sort($allClasses);
    
    foreach ($allClasses as $className) {
        $inManual = isset($manual[$className]);
        $inGenerated = isset($generated[$className]);
        
        $status = '✅';
        if (!$inManual) $status = '❌ Missing from manual';
        elseif (!$inGenerated) $status = '⚠️ Extra in manual';
        
        $report .= "- **$className**: $status\n";
        
        if ($inManual && $inGenerated) {
            $manualMethods = count($manual[$className]['methods']);
            $generatedMethods = count($generated[$className]['methods']);
            $report .= "  - Methods: $manualMethods manual, $generatedMethods generated\n";
        }
    }
    
    return $report;
}

// Main execution
if ($argc < 3) {
    echo "Usage: php verify-stubs.php <manual-stubs> <generated-stubs>\n";
    echo "Example: php verify-stubs.php stubs/identifier.stub.php stubs/identifier.stub.php.generated\n";
    exit(1);
}

$manualFile = $argv[1];
$generatedFile = $argv[2];

try {
    echo "Verifying stubs...\n";
    echo "Manual: $manualFile\n";
    echo "Generated: $generatedFile\n\n";
    
    $manual = parseStubFile($manualFile);
    $generated = parseStubFile($generatedFile);
    
    $issues = compareStubs($manual, $generated);
    
    if (empty($issues)) {
        echo "✅ SUCCESS: Manual stubs are in sync with extension API!\n";
        echo "Manual stubs: " . count($manual) . " classes\n";
        echo "Generated stubs: " . count($generated) . " classes\n";
        exit(0);
    } else {
        echo "❌ ISSUES FOUND:\n\n";
        foreach ($issues as $issue) {
            echo "$issue\n";
        }
        echo "\n";
        echo "Total issues: " . count($issues) . "\n";
        echo "\nConsider running 'zig build generate-stubs' to see the expected API.\n";
        exit(1);
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
