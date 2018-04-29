<?php

declare(strict_types=1);

use Isolated\Symfony\Component\Finder\Finder;

return [
    'prefix' => 'RectorPrefixed',
    'finders' => [
        Finder::create()
            ->files()
            ->ignoreVCS(true)
            ->notName('/LICENSE|.*\\.md|.*\\.dist|Makefile|composer\\.json|composer\\.lock/')
            ->in('*')
            ->exclude([
                'docs',
                'Tests'
            ])
        ,
    ],
    'patchers' => [
        function (string $filePath, string $prefix, string $contents): string {
            // Change the contents here.

            return $contents;
        },
    ],
];
