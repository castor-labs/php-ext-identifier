# Changelog

## [1.1.0](https://github.com/castor-labs/php-ext-identifier/compare/v1.0.2...v1.1.0) (2025-11-16)


### Features

* add Alpine Linux (musl) support for PHP extensions ([95d2ba6](https://github.com/castor-labs/php-ext-identifier/commit/95d2ba6b2df902f7ef817125297d95845dc7c693))


### Bug Fixes

* use PHP version matrix for correct extension builds ([3993fed](https://github.com/castor-labs/php-ext-identifier/commit/3993fed9b98192d9a743afa49df67c9df6199cdb))

## [1.0.2](https://github.com/castor-labs/php-ext-identifier/compare/v1.0.1...v1.0.2) (2025-11-16)


### Bug Fixes

* add workflow_dispatch triggers for manual debugging ([4258404](https://github.com/castor-labs/php-ext-identifier/commit/425840474c33f9e5b15590ccfff981862ca3099b))

## [1.0.1](https://github.com/castor-labs/php-ext-identifier/compare/1.0.0...v1.0.1) (2025-11-16)


### Bug Fixes

* migrate from semantic-release to release-please ([#14](https://github.com/castor-labs/php-ext-identifier/issues/14)) ([ffef8ff](https://github.com/castor-labs/php-ext-identifier/commit/ffef8ffdd859ca1e33bffca04377635d1821c097))

## 1.0.0 (2025-11-16)

### ⚠ BREAKING CHANGES

* All namespaces have been updated:
- Php\Identifier\* -> Identifier\*
- Php\Encoding\* -> Encoding\*

This affects all classes in the extension. Users must update
their code to use the new namespace structure.

🤖 Generated with [Claude Code](https://claude.com/claude-code)

Co-Authored-By: Claude <noreply@anthropic.com>

### Features

* **#12:** implement automated release workflow ([#13](https://github.com/castor-labs/php-ext-identifier/issues/13)) ([a46d12d](https://github.com/castor-labs/php-ext-identifier/commit/a46d12d4e59d300efbb4f7e7238461c397a32007)), closes [#12](https://github.com/castor-labs/php-ext-identifier/issues/12) [#12](https://github.com/castor-labs/php-ext-identifier/issues/12) [#11](https://github.com/castor-labs/php-ext-identifier/issues/11)
* add continuous integration workflow ([#10](https://github.com/castor-labs/php-ext-identifier/issues/10)) ([#11](https://github.com/castor-labs/php-ext-identifier/issues/11)) ([24520e8](https://github.com/castor-labs/php-ext-identifier/commit/24520e86f96688b2558bca0daa7e0d53fbaa81c3))
* dynamic PHP path detection using php-config ([33dab34](https://github.com/castor-labs/php-ext-identifier/commit/33dab34d207337ecd12cd70d0364e2bb12b1a3fc))
* dynamic source file discovery in build system ([2be8015](https://github.com/castor-labs/php-ext-identifier/commit/2be801585907a7aed1c3431095c9f923aaea1302))
* implement `Stringable` in `Bit128` class ([72cc59a](https://github.com/castor-labs/php-ext-identifier/commit/72cc59ab5071593ca5185ab2adad58093e57624f))
* implement Codec class improvements ([#8](https://github.com/castor-labs/php-ext-identifier/issues/8)) ([5f1f03c](https://github.com/castor-labs/php-ext-identifier/commit/5f1f03c5b58441168b1eb1170070764247331103)), closes [#2](https://github.com/castor-labs/php-ext-identifier/issues/2)
* initial commit ([990d32d](https://github.com/castor-labs/php-ext-identifier/commit/990d32d31d089c50e4e3693d9dbc0a161f49334f))
* migrate from PECL to PIE packaging ([#9](https://github.com/castor-labs/php-ext-identifier/issues/9)) ([db93b5b](https://github.com/castor-labs/php-ext-identifier/commit/db93b5b7c07062a4b9bde85a10af3a67122f0ef9))
* random overflow protection ([3235b8f](https://github.com/castor-labs/php-ext-identifier/commit/3235b8f4a3f5c4ec37ba6970131785a04cd519b8))
* remove Php prefix from all namespaces ([2e7cef5](https://github.com/castor-labs/php-ext-identifier/commit/2e7cef5e848d78f58d445f060f170f58f6927c14))

### Bug Fixes

* handle null from shell_exec in test runner ([36f83a9](https://github.com/castor-labs/php-ext-identifier/commit/36f83a9828de4394a5c8d01a235cbcbbf8d8055c))
* ulid segmentation fault in ulid ([7d2b78a](https://github.com/castor-labs/php-ext-identifier/commit/7d2b78a3d0e36c6ef667352097a19d57919f415f))
* use ext/random headers for PHP 8.2+ ([27b7b26](https://github.com/castor-labs/php-ext-identifier/commit/27b7b269a39f40d66693e5b3baa5de77048a659b))
