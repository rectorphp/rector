<?php declare(strict_types=1);

use Symfony\Component\Finder\Finder;

return [
    // By default when running php-scoper add-prefix, it will prefix all relevant code found in the current working
    // directory. You can however define which files should be scoped by defining a collection of Finders in the
    // following configuration key.
    //
    // For more see: https://github.com/humbug/php-scoper#finders-and-paths
    'finders' => [
        Finder::create()
            ->files()
            ->in(__DIR__ . '/bin')
            ->in(__DIR__ . '/src')
            ->in(__DIR__ . '/packages')
            ->exclude('tests'),

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

        Finder::create()->append([
            __DIR__ . '/composer.json',
            __DIR__ . '/vendor/friendsofphp/php-cs-fixer/tests/TestCase.php'
        ]),

        // workaround for php-cs-fixer's misslocation of source files in /tests directory
        Finder::create()
            ->files()
            ->in(__DIR__ . '/vendor/friendsofphp/php-cs-fixer/tests/Test')
    ],


    // PHP-Scoper's goal is to make sure that all code for a project lies in a distinct PHP namespace. However, you
    // may want to share a common API between the bundled code of your PHAR and the consumer code. For example if
    // you have a PHPUnit PHAR with isolated code, you still want the PHAR to be able to understand the
    // PHPUnit\Framework\TestCase class.
    //
    // A way to achieve this is by specifying a list of classes to not prefix with the following configuration key. Note
    // that this does not work with functions or constants neither with classes belonging to the global namespace.
    //
    // Fore more see https://github.com/humbug/php-scoper#whitelist
    'whitelist' => [
        'PHPUnit\Framework\TestCase',
    ],
];
