<?php declare(strict_types=1);

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
            ->in(__DIR__ . '/bin')
            ->in(__DIR__ . '/src')
            ->in(__DIR__ . '/packages')
            ->exclude('tests'),

        // /vendor files
        Finder::create()
            ->files()
            ->name('*.php')
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

        // required for php-scoper - "autoload" sections and "composer dump"
        Finder::create()->append([
            __DIR__ . '/composer.json',
        ]),

        // workaround for php-cs-fixer's misslocation of source files in /tests directory
        Finder::create()
            ->files()
            ->in(__DIR__ . '/vendor/friendsofphp/php-cs-fixer/tests/Test')
            ->append([__DIR__ . '/vendor/friendsofphp/php-cs-fixer/tests/TestCase.php'])
    ],
];
