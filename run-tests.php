#!/usr/bin/env php
<?php
/**
 * Simple PHP test runner for .phpt files
 * Replacement for the system run-tests.php
 */

function runTest($testFile, $extensionArg = '') {
    $content = file_get_contents($testFile);
    
    // Parse the test file
    $sections = [];
    $currentSection = null;
    $lines = explode("\n", $content);
    
    foreach ($lines as $line) {
        if (preg_match('/^--([A-Z_]+)--$/', $line, $matches)) {
            $currentSection = $matches[1];
            $sections[$currentSection] = '';
        } elseif ($currentSection !== null) {
            $sections[$currentSection] .= $line . "\n";
        }
    }
    
    // Check if test should be skipped
    if (isset($sections['SKIPIF'])) {
        $skipCode = trim($sections['SKIPIF']);

        // Execute SKIPIF in separate process like the main test
        $tempFile = tempnam(sys_get_temp_dir(), 'phpskip_');
        file_put_contents($tempFile, $skipCode);

        $cmd = 'php';
        if ($extensionArg) {
            $cmd .= " -d $extensionArg";
        }
        $cmd .= " $tempFile 2>&1";

        $output = trim(shell_exec($cmd));
        unlink($tempFile);

        if (strpos($output, 'skip') === 0) {
            return ['status' => 'SKIP', 'reason' => trim(substr($output, 4))];
        }
    }
    
    // Run the test
    if (!isset($sections['FILE'])) {
        return ['status' => 'FAIL', 'reason' => 'No FILE section found'];
    }
    
    $testCode = trim($sections['FILE']);
    $expectedOutput = isset($sections['EXPECT']) ? trim($sections['EXPECT']) : '';

    // Remove PHP opening tag if present
    if (str_starts_with($testCode, '<?php')) {
        $testCode = substr($testCode, 5);
    }

    // Execute the test code in a separate PHP process to handle extensions
    $tempFile = tempnam(sys_get_temp_dir(), 'phptest_');
    file_put_contents($tempFile, "<?php\n" . $testCode);

    $cmd = 'php';
    if ($extensionArg) {
        $cmd .= " -d $extensionArg";
    }
    $cmd .= " $tempFile 2>&1";

    $actualOutput = trim(shell_exec($cmd));
    unlink($tempFile);

    $error = null;
    
    if ($error) {
        return ['status' => 'FAIL', 'reason' => "Error: $error"];
    }
    
    if ($actualOutput === $expectedOutput) {
        return ['status' => 'PASS'];
    } else {
        return [
            'status' => 'FAIL', 
            'reason' => "Output mismatch",
            'expected' => $expectedOutput,
            'actual' => $actualOutput
        ];
    }
}

// Main execution
$testDir = $argv[1] ?? 'tests/';
$extensionArg = '';

// Parse command line arguments
for ($i = 1; $i < $argc; $i++) {
    if ($argv[$i] === '-d' && isset($argv[$i + 1])) {
        $extensionArg = $argv[$i + 1];
        $i++; // Skip next argument
    } elseif (!str_starts_with($argv[$i], '-')) {
        $testDir = $argv[$i];
    }
}

// Extension loading is handled by the -d flag passed to PHP
// We don't need to manually load it here

// Find test files
$testFiles = glob($testDir . '*.phpt');
if (empty($testFiles)) {
    echo "No test files found in $testDir\n";
    exit(1);
}

sort($testFiles);

// Print header
echo str_repeat('=', 69) . "\n";
echo "PHP         : " . PHP_BINARY . "\n";
echo "PHP_SAPI    : " . PHP_SAPI . "\n";
echo "PHP_VERSION : " . PHP_VERSION . "\n";
echo "ZEND_VERSION: " . (defined('ZEND_VERSION') ? ZEND_VERSION : phpversion('zend')) . "\n";
echo "PHP_OS      : " . PHP_OS . "\n";
echo "CWD         : " . getcwd() . "\n";
echo str_repeat('=', 69) . "\n";
echo "Running selected tests.\n";

// Run tests
$totalTests = count($testFiles);
$passedTests = 0;
$failedTests = 0;
$skippedTests = 0;

foreach ($testFiles as $i => $testFile) {
    $testNum = $i + 1;
    $testName = basename($testFile);
    
    echo "TEST $testNum/$totalTests [$testFile]";
    
    $result = runTest($testFile, $extensionArg);
    
    switch ($result['status']) {
        case 'PASS':
            echo "PASS " . (isset($sections['TEST']) ? trim($sections['TEST']) : $testName) . " [$testFile]\n";
            $passedTests++;
            break;
        case 'SKIP':
            echo "SKIP " . $result['reason'] . " [$testFile]\n";
            $skippedTests++;
            break;
        case 'FAIL':
            echo "FAIL " . $result['reason'] . " [$testFile]\n";
            if (isset($result['expected']) && isset($result['actual'])) {
                echo "Expected:\n" . $result['expected'] . "\n";
                echo "Actual:\n" . $result['actual'] . "\n";
            }
            $failedTests++;
            break;
    }
}

// Print summary
echo str_repeat('=', 69) . "\n";
echo "Number of tests : " . str_pad($totalTests, 4) . str_pad($totalTests, 17) . "\n";
echo "Tests skipped   : " . str_pad($skippedTests, 4) . " (" . str_pad(number_format($skippedTests / $totalTests * 100, 1), 5) . "%) --------\n";
echo "Tests warned    : " . str_pad(0, 4) . " (  0.0%) (  0.0%)\n";
echo "Tests failed    : " . str_pad($failedTests, 4) . " (" . str_pad(number_format($failedTests / $totalTests * 100, 1), 5) . "%) (" . str_pad(number_format($failedTests / $totalTests * 100, 1), 5) . "%)\n";
echo "Tests passed    : " . str_pad($passedTests, 4) . " (" . str_pad(number_format($passedTests / $totalTests * 100, 1), 5) . "%) (" . str_pad(number_format($passedTests / $totalTests * 100, 1), 5) . "%)\n";
echo str_repeat('-', 69) . "\n";
echo "Time taken      : " . str_pad(0, 4) . " seconds\n";
echo str_repeat('=', 69) . "\n";

// Exit with appropriate code
exit($failedTests > 0 ? 1 : 0);
