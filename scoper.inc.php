<?php declare(strict_types=1);

require __DIR__ . '/vendor/nette/utils/src/Utils/StaticClass.php';
require __DIR__ . '/vendor/nette/utils/src/Utils/Strings.php';

use Nette\Utils\Strings;
use Symfony\Component\Finder\Finder;

return [
    // By default when running php-scoper add-prefix, it will prefix all relevant code found in the current working
    // directory. You can however define which files should be scoped by defining a collection of Finders in the
    // following configuration key.
    //
    // For more see: https://github.com/humbug/php-scoper#finders-and-paths
    'finders' => [
        // Rector source
        Finder::create()
            ->files()
            ->notName('*.sh')
            ->in('bin')
            ->in('src')
            ->in('packages')
            ->exclude('tests'),

        // /vendor files; note: composer.json is needed for "composer dump"
        Finder::create()
            ->files()
            ->name('*.{php,yml}')
            ->name('{composer,installed}.json')
            ->ignoreVCS(true)
            ->exclude([
                'doc',
                'test',
                'Test',
                'test_old',
                'tests',
                'Tests'
            ])
            ->in(__DIR__ . '/vendor'),

        // workaround for php-cs-fixer's misslocation of source files in /tests directory
        Finder::create()
            ->files()
            ->in(__DIR__ . '/vendor/friendsofphp/php-cs-fixer/tests/Test'),

        // required for php-scoper - "autoload" sections and "composer dump"
        Finder::create()->append([
            'composer.json',
            'composer.lock',
            // required by php-cs-fixer; only 1 append is allowed probably
            __DIR__ . '/vendor/friendsofphp/php-cs-fixer/tests/TestCase.php',
        ]),
    ],

    // For more see: https://github.com/humbug/php-scoper#patchers
    // change code based on conditions (suffix, content)
    'patchers' => [
        function (string $filePath, string $prefix, string $contents): string {
            // only .yml files
            if (! strpos($filePath, '.yml')) {
                return $contents;
            }

            if (Strings::contains($filePath, 'Rector/src/config/level')) {
                // @todo: resolve levels, Rector classes would need to be renamed? or skip "Rector" namespace?
                // https://github.com/humbug/php-scoper/issues/166#issuecomment-366894125
                return $contents;
            }

            // match all possible type references in .yml
            // https://regex101.com/r/IdrE7s/4

            // "@ServiceReference"
            $patternReference = '#\@\K([A-Z][a-zA-Z]+)#';
            $prefixedContents = preg_replace_callback($patternReference, function ($match) use ($prefix) {
                return $prefix . '\\' . $match[1];
            }, $contents);

            // "SomeService\" + @todo: add for single class services, this only matches with "\" in the end
            // "\K" => https://stackoverflow.com/a/45031856/1348344
            $patternSingleService = '#\s\K([A-Z][A-Za-z]+)\\\\#';
            $prefixedContents = preg_replace_callback($patternSingleService, function ($match) use ($prefix) {
                return $prefix . '\\' . $match[1] . '\\';
            }, $prefixedContents);

            return $prefixedContents;
        },

        // remove shebang workaround
        // issue ref: https://github.com/humbug/php-scoper/issues/166#issuecomment-366922368
        function (string $filePath, string $prefix, string $contents): string {
            if (! Strings::endsWith($filePath, 'bin/rector')) {
                return $contents;
            }

            $contents = ltrim($contents,'#!/usr/bin/env php');
            return trim($contents);
        }
    ],

    // For more see: https://github.com/humbug/php-scoper#whitelist
    // do not prefix any 'Rector\*' class, to allow access via external API
    // @todo: reflect in yaml prefixer above, that prefixes those cases now
    'whitelist' => [
        'Rector\*',
    ],
];
