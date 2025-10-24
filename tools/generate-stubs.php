#!/usr/bin/env php
<?php
/**
 * Enhanced PHP Extension Stub Generator
 * 
 * Generates PHP stubs from extension reflection AND C source documentation.
 * This combines the accuracy of reflection with rich documentation from C comments.
 */

class CSourceDocParser {
    private array $methodDocs = [];
    private array $classDocs = [];
    
    public function parseSourceDirectory(string $sourceDir): void {
        $files = glob($sourceDir . '/*.c');
        foreach ($files as $file) {
            $this->parseSourceFile($file);
        }
    }
    
    private function parseSourceFile(string $filename): void {
        $content = file_get_contents($filename);
        if (!$content) return;
        
        // Parse class documentation
        $this->parseClassDocs($content);
        
        // Parse method documentation
        $this->parseMethodDocs($content);
    }
    
    private function parseClassDocs(string $content): void {
        // Look for class documentation comments before class registration
        $pattern = '/\/\*\*\s*\n(.*?)\*\/\s*.*?register.*?class.*?(\w+)/s';
        if (preg_match_all($pattern, $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $docComment = $this->parseDocComment($match[1]);
                $className = $match[2];
                $this->classDocs[$className] = $docComment;
            }
        }
    }
    
    private function parseMethodDocs(string $content): void {
        // Look for documentation comments before PHP_METHOD declarations
        $pattern = '/\/\*\*\s*\n(.*?)\*\/\s*.*?PHP_METHOD\s*\(\s*([^,]+),\s*([^)]+)\s*\)/s';
        if (preg_match_all($pattern, $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $docComment = $this->parseDocComment($match[1]);
                $className = str_replace('_', '\\', $match[2]);
                $methodName = $match[3];
                
                // Convert C class name to PHP namespace
                $className = $this->convertCClassNameToPhp($className);
                
                $this->methodDocs[$className][$methodName] = $docComment;
            }
        }
    }
    
    private function parseDocComment(string $docText): array {
        $lines = explode("\n", $docText);
        $doc = [
            'description' => '',
            'params' => [],
            'return' => '',
            'throws' => [],
            'example' => '',
            'since' => '',
            'see' => []
        ];
        
        $currentSection = 'description';
        $descriptionLines = [];
        
        foreach ($lines as $line) {
            $line = trim($line, " \t\n\r\0\x0B*");
            
            if (empty($line)) continue;
            
            if (preg_match('/^@(\w+)\s*(.*)$/', $line, $matches)) {
                $tag = $matches[1];
                $content = $matches[2];
                
                switch ($tag) {
                    case 'param':
                        if (preg_match('/^(\S+)\s+\$(\w+)\s*(.*)$/', $content, $paramMatches)) {
                            $doc['params'][] = [
                                'type' => $paramMatches[1],
                                'name' => $paramMatches[2],
                                'description' => $paramMatches[3]
                            ];
                        }
                        break;
                    case 'return':
                        $doc['return'] = $content;
                        break;
                    case 'throws':
                        $doc['throws'][] = $content;
                        break;
                    case 'example':
                        $currentSection = 'example';
                        $doc['example'] = $content;
                        break;
                    case 'since':
                        $doc['since'] = $content;
                        break;
                    case 'see':
                        $doc['see'][] = $content;
                        break;
                }
            } else {
                if ($currentSection === 'description') {
                    $descriptionLines[] = $line;
                } elseif ($currentSection === 'example') {
                    $doc['example'] .= "\n" . $line;
                }
            }
        }
        
        $doc['description'] = implode("\n", $descriptionLines);
        return $doc;
    }
    
    private function convertCClassNameToPhp(string $cClassName): string {
        // Convert Php_Identifier_Uuid_Version4 to Php\Identifier\Uuid\Version4
        $phpClassName = str_replace('_', '\\', $cClassName);

        // Handle special case for Encoding\Codec
        if ($phpClassName === 'Php\\Identifier\\Encoding\\Codec') {
            return 'Php\\Encoding\\Codec';
        }

        return $phpClassName;
    }
    
    public function getMethodDoc(string $className, string $methodName): ?array {
        return $this->methodDocs[$className][$methodName] ?? null;
    }
    
    public function getClassDoc(string $className): ?array {
        return $this->classDocs[$className] ?? null;
    }
}

function generateEnhancedClassStub(ReflectionClass $class, CSourceDocParser $docParser, int $indent = 0): string {
    $output = '';
    $indentStr = str_repeat('    ', $indent);
    
    // Get class documentation
    $classDoc = $docParser->getClassDoc($class->getName());
    
    // Class documentation comment
    if ($classDoc) {
        $output .= $indentStr . "/**\n";
        if ($classDoc['description']) {
            $output .= $indentStr . " * " . str_replace("\n", "\n" . $indentStr . " * ", $classDoc['description']) . "\n";
        }
        if ($classDoc['since']) {
            $output .= $indentStr . " * \n";
            $output .= $indentStr . " * @since " . $classDoc['since'] . "\n";
        }
        $output .= $indentStr . " */\n";
    }
    
    // Class declaration
    $classDecl = $indentStr;
    if ($class->isAbstract()) {
        $classDecl .= 'abstract ';
    }
    if ($class->isFinal()) {
        $classDecl .= 'final ';
    }
    $classDecl .= 'class ' . $class->getShortName();
    
    // Parent class
    if ($parent = $class->getParentClass()) {
        $classDecl .= ' extends \\' . $parent->getName();
    }
    
    // Interfaces
    $interfaces = $class->getInterfaceNames();
    if (!empty($interfaces)) {
        $classDecl .= ' implements ' . implode(', ', array_map(fn($i) => '\\' . $i, $interfaces));
    }
    
    $output .= $classDecl . "\n" . $indentStr . "{\n";
    
    // Constants
    foreach ($class->getConstants() as $name => $value) {
        $output .= $indentStr . "    public const " . $name . " = ";
        $output .= var_export($value, true) . ";\n";
    }
    
    if ($class->getConstants()) {
        $output .= "\n";
    }
    
    // Methods
    $methods = $class->getMethods();
    foreach ($methods as $method) {
        if ($method->getDeclaringClass()->getName() !== $class->getName()) {
            continue; // Skip inherited methods
        }
        
        $output .= generateEnhancedMethodStub($method, $docParser, $indent + 1);
    }
    
    $output .= $indentStr . "}\n";
    
    return $output;
}

function generateEnhancedMethodStub(ReflectionMethod $method, CSourceDocParser $docParser, int $indent = 0): string {
    $indentStr = str_repeat('    ', $indent);
    $output = '';
    
    // Get method documentation
    $methodDoc = $docParser->getMethodDoc($method->getDeclaringClass()->getName(), $method->getName());
    
    // Method documentation comment
    if ($methodDoc) {
        $output .= $indentStr . "/**\n";
        
        if ($methodDoc['description']) {
            $output .= $indentStr . " * " . str_replace("\n", "\n" . $indentStr . " * ", $methodDoc['description']) . "\n";
            $output .= $indentStr . " * \n";
        }
        
        // Parameters
        foreach ($methodDoc['params'] as $param) {
            $output .= $indentStr . " * @param {$param['type']} \${$param['name']} {$param['description']}\n";
        }
        
        // Return
        if ($methodDoc['return']) {
            $output .= $indentStr . " * @return " . $methodDoc['return'] . "\n";
        }
        
        // Throws
        foreach ($methodDoc['throws'] as $throws) {
            $output .= $indentStr . " * @throws " . $throws . "\n";
        }
        
        // Example
        if ($methodDoc['example']) {
            $output .= $indentStr . " * \n";
            $output .= $indentStr . " * @example\n";
            $output .= $indentStr . " * ```php\n";
            $exampleLines = explode("\n", trim($methodDoc['example']));
            foreach ($exampleLines as $line) {
                $output .= $indentStr . " * " . $line . "\n";
            }
            $output .= $indentStr . " * ```\n";
        }
        
        // Since
        if ($methodDoc['since']) {
            $output .= $indentStr . " * @since " . $methodDoc['since'] . "\n";
        }
        
        $output .= $indentStr . " */\n";
    }
    
    // Method signature (same as before)
    $signature = $indentStr;
    
    // Visibility
    if ($method->isPublic()) {
        $signature .= 'public ';
    } elseif ($method->isProtected()) {
        $signature .= 'protected ';
    } elseif ($method->isPrivate()) {
        $signature .= 'private ';
    }
    
    // Static
    if ($method->isStatic()) {
        $signature .= 'static ';
    }
    
    // Function name
    $signature .= 'function ' . $method->getName() . '(';
    
    // Parameters
    $params = [];
    foreach ($method->getParameters() as $param) {
        $paramStr = '';
        
        // Type hint
        if ($param->hasType()) {
            $type = $param->getType();
            if ($type instanceof ReflectionNamedType) {
                $typeName = $type->getName();
                if (!$type->isBuiltin()) {
                    $typeName = '\\' . $typeName;
                }
                if ($type->allowsNull() && $typeName !== 'mixed') {
                    $typeName = '?' . $typeName;
                }
                $paramStr .= $typeName . ' ';
            }
        }
        
        // Reference
        if ($param->isPassedByReference()) {
            $paramStr .= '&';
        }
        
        // Variadic
        if ($param->isVariadic()) {
            $paramStr .= '...';
        }
        
        // Parameter name
        $paramStr .= '$' . $param->getName();
        
        // Default value
        if ($param->isDefaultValueAvailable()) {
            $defaultValue = $param->getDefaultValue();
            $paramStr .= ' = ' . var_export($defaultValue, true);
        } elseif ($param->isOptional()) {
            $paramStr .= ' = null';
        }
        
        $params[] = $paramStr;
    }
    
    $signature .= implode(', ', $params) . ')';
    
    // Return type
    if ($method->hasReturnType()) {
        $returnType = $method->getReturnType();
        if ($returnType instanceof ReflectionNamedType) {
            $typeName = $returnType->getName();
            if (!$returnType->isBuiltin()) {
                $typeName = '\\' . $typeName;
            }
            if ($returnType->allowsNull() && $typeName !== 'mixed') {
                $typeName = '?' . $typeName;
            }
            $signature .= ': ' . $typeName;
        }
    }
    
    $signature .= ' {}';
    
    $output .= $signature . "\n\n";
    
    return $output;
}

// Main execution
if ($argc < 4) {
    echo "Usage: php generate-stubs.php <extension-name> <source-dir> <output-file>\n";
    echo "Example: php generate-stubs.php identifier src/ stubs/identifier_gen.stub.php\n";
    exit(1);
}

$extensionName = $argv[1];
$sourceDir = $argv[2];
$outputFile = $argv[3];

try {
    if (!extension_loaded($extensionName)) {
        throw new RuntimeException("Extension '$extensionName' is not loaded");
    }
    
    echo "Parsing C source documentation from: $sourceDir\n";
    $docParser = new CSourceDocParser();
    $docParser->parseSourceDirectory($sourceDir);
    
    echo "Generating stubs with full documentation for extension: $extensionName\n";
    
    $extension = new ReflectionExtension($extensionName);
    $output = "<?php\n\n";
    $output .= "/**\n";
    $output .= " * Stubs for $extensionName extension\n";
    $output .= " * \n";
    $output .= " * Generated from extension reflection with full C source documentation.\n";
    $output .= " * \n";
    $output .= " * @version " . ($extension->getVersion() ?: 'unknown') . "\n";
    $output .= " * @generated " . date('Y-m-d H:i:s') . "\n";
    $output .= " */\n\n";
    
    // Get all classes and organize by namespace
    $classes = $extension->getClasses();
    $namespaces = [];
    
    foreach ($classes as $class) {
        $namespace = $class->getNamespaceName();
        if (!isset($namespaces[$namespace])) {
            $namespaces[$namespace] = [];
        }
        $namespaces[$namespace][] = $class;
    }
    
    // Generate stubs by namespace
    foreach ($namespaces as $namespace => $namespaceClasses) {
        if ($namespace) {
            $output .= "namespace $namespace\n{\n";
            $indent = 1;
        } else {
            $indent = 0;
        }
        
        foreach ($namespaceClasses as $class) {
            $output .= generateEnhancedClassStub($class, $docParser, $indent);
            $output .= "\n";
        }
        
        if ($namespace) {
            $output .= "}\n\n";
        }
    }
    
    // Ensure output directory exists
    $outputDir = dirname($outputFile);
    if (!is_dir($outputDir)) {
        mkdir($outputDir, 0755, true);
    }
    
    file_put_contents($outputFile, $output);
    echo "Stubs with full documentation generated successfully: $outputFile\n";
    
    // Show some stats
    $lines = substr_count($output, "\n");
    $classes = substr_count($output, 'class ');
    $methods = substr_count($output, 'function ');
    $docBlocks = substr_count($output, '/**');
    
    echo "Generated: $lines lines, $classes classes, $methods methods, $docBlocks doc blocks\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
