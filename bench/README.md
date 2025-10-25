# Performance Benchmarks

This directory contains comprehensive performance benchmarks comparing the PHP Identifier Extension against popular PHP UUID/ULID libraries.

**📊 For complete benchmark results and analysis, see [BENCH.md](../BENCH.md)**

## Libraries Compared

- **PHP Identifier Extension** (this project) - Native C implementation
- **ramsey/uuid** - Popular PHP UUID library
- **symfony/uid** - Symfony's UUID/ULID implementation
- **ext-uuid** - Native PHP UUID extension (optional, if available)

## Benchmark Approaches

### 1. Zig Build System (Recommended)

Integrated benchmarking using the project's build system with Docker for clean environments.

#### Run Benchmarks
```bash
# Quick performance comparison
zig build bench

# Comprehensive PHPBench analysis
zig build bench-full

# Generate HTML report with charts
zig build bench-html

# Local benchmarks (no Docker)
zig build bench-local
```

#### Features
- **Integrated workflow** - Part of the main build system
- **Docker isolation** - Clean, consistent environment
- **Statistical rigor** - Multiple iterations with confidence intervals
- **Memory profiling** - Track memory usage alongside execution time
- **HTML reports** - Beautiful charts and detailed analysis
- **No setup required** - Everything automated

### 2. Direct Docker Commands

For advanced users who want direct control over Docker execution.

#### Run
```bash
# Build the benchmark image
docker build -f bench/Dockerfile -t identifier-bench .

# Quick comparison
docker run --rm -v ./bench/results:/app/bench/results identifier-bench

# Full PHPBench analysis
docker run --rm -v ./bench/results:/app/bench/results identifier-bench vendor/bin/phpbench run --report=default

# Interactive shell
docker run --rm -it -v ./bench/results:/app/bench/results identifier-bench /bin/bash
```

#### Features
- **Direct control** - Full Docker command flexibility
- **Simple setup** - No Docker Compose required
- **Interactive mode** - Shell access for custom benchmarks

## Benchmark Categories

### UUID Generation
- **Version 1** - Time-based UUIDs
- **Version 4** - Random UUIDs  
- **Version 7** - Timestamp-ordered UUIDs

### UUID Parsing
- **String parsing** - From standard UUID format
- **Hex parsing** - From hexadecimal strings
- **Validation** - UUID format validation

### ULID Operations
- **Generation** - Creating new ULIDs
- **Parsing** - From string representation
- **Timestamp extraction** - Getting embedded timestamp

## Actual Results

The PHP Identifier Extension delivers exceptional performance:

- **🏆 2.8M+ UUID generations per second** - Up to 28x faster than popular PHP libraries
- **⚡ 2.4M+ parsing operations per second** - Extremely efficient string processing
- **💎 57M+ property access operations per second** - Lightning-fast data access
- **🔧 Lower memory usage** due to native C implementation
- **📈 Excellent scalability** under enterprise workloads

**See [BENCH.md](../BENCH.md) for detailed results and analysis.**

## Sample Output

```
📊 UUID Generation Performance:
  UUID v4 (Random)    :   0.0179s |       2,790,176 ops/sec 🏆
  UUID v1 (Time-based):   0.0200s |       2,494,145 ops/sec (1.1x slower)
  UUID v7 (Unix timestamp):   0.0202s |       2,479,284 ops/sec (1.1x slower)

📊 UUID Parsing Performance:
  fromString          :   0.0212s |       2,358,179 ops/sec 🏆
  fromHex             :   0.0347s |       1,438,850 ops/sec (1.6x slower)

📊 UUID Operations Performance:
  getVersion()        :   0.0009s |      57,409,034 ops/sec 🏆
  getBytes()          :   0.0018s |      27,898,789 ops/sec (2.1x slower)
```

## Running Benchmarks

### Docker Approach (Recommended)

The easiest and most reliable way to run benchmarks is using Docker, which ensures a clean environment with proper extensions and no debugging overhead.

#### Prerequisites
- Docker installed

#### Quick Start
```bash
# Quick performance comparison
zig build bench

# Comprehensive analysis with statistics
zig build bench-full

# View beautiful HTML report
zig build bench-html

# Open HTML report in browser
./bench/open-report.sh

# Interactive shell for custom benchmarks
docker run --rm -it -v ./bench/results:/app/bench/results identifier-bench /bin/bash
```

#### Alternative Commands
```bash
# Using the runner script
./run-benchmarks.sh           # Quick comparison
./run-benchmarks.sh --full    # Full analysis + HTML report

# Using Docker Compose directly
docker-compose -f bench/docker-compose.yml --profile quick run --rm quick
docker-compose -f bench/docker-compose.yml --profile phpbench run --rm phpbench
docker-compose -f bench/docker-compose.yml --profile html run --rm phpbench-html
```

### Local Approach (Advanced)

For local development, you can run benchmarks directly:

#### Prerequisites
```bash
# Build the extension
cd ..
zig build

# Install benchmark dependencies
cd bench
composer install
```

#### Local Benchmark Commands
```bash
# Run all benchmarks with detailed analysis
composer bench

# Generate comprehensive HTML report
composer bench-html
open results/report.html

# Fast comparison for development
composer quick-bench
```

### Docker Environment Benefits

#### Clean Environment
- **No Xdebug interference** - Debugging extensions disabled
- **Optimized PHP configuration** - OPcache enabled, memory optimized
- **Consistent dependencies** - Same versions across all runs
- **Isolated execution** - No interference from host system

#### Comprehensive Setup
- **Multiple PHP extensions** - UUID extension included for comparison
- **Latest Zig compiler** - Builds extension from source
- **Professional tools** - PHPBench with full statistical analysis
- **Automated reporting** - HTML reports with charts and graphs

### Continuous Integration
```bash
# Create baseline on main branch
zig build bench-full
cp bench/results/phpbench.db bench/baseline.db

# Compare feature branch against baseline
zig build bench-full
# Compare results manually or with PHPBench compare features
```

## Interpreting Results

- **Lower execution time** = better performance
- **Higher ops/sec** = better throughput
- **Speedup multiplier** = how much faster than competitors
- **Memory usage** = efficiency of implementation
- **Confidence intervals** = reliability of measurements

## Contributing

When adding new benchmarks:

1. **Add to PHPBench** - Create new benchmark class in `src/`
2. **Add to quick script** - Update `simple/quick_comparison.php`
3. **Update documentation** - Document new benchmark scenarios
4. **Test thoroughly** - Ensure benchmarks are fair and accurate

## Notes

- Benchmarks should be run on a quiet system for accurate results
- Multiple runs help identify performance variations
- Memory profiling helps identify efficiency improvements
- Baseline comparisons track performance regressions
