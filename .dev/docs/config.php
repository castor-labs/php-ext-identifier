<?php

declare(strict_types=1);

use Castor\Docs\Documentation;

return static function (Documentation $docs): void {
    $docs->addComposerJsonPath(__DIR__ . '/../../composer.json');

    $docs->section('Introduction', [
        'index.md',
    ]);

    $docs->section('Guides', [
        'guides/choosing-identifiers.md',
        'guides/parsing-formatting-and-storage.md',
        'guides/deterministic-generation.md',
    ]);
};
