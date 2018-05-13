<?php declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use Isolated\Symfony\Component\Finder\Finder;

return [
    'prefix' => 'RectorPrefixed',
    'finders' => [
        Finder::create()
            ->files()
            ->ignoreVCS(true)
            // ↓ this is regex!
            ->notName('#LICENSE|.*\\.md|.*\\.dist|Makefile|composer\\.json|composer\\.lock|.*\\.sh#')
            ->in(__DIR__ .'/bin')
            ->in(__DIR__ .'/config')
            ->in(__DIR__ .'/packages')
            ->in(__DIR__ .'/src')
            ->in(__DIR__ .'/vendor')
            ->exclude([
                'docs',
                'Tests',
                'tests',
                'Test',
                'test'
            ])
        ,
        // to make "composer dump" work
        Finder::create()->append([
            'composer.json',
            // Fixes non-standard php-cs-fixer tests in /src
            __DIR__ . '/vendor/friendsofphp/php-cs-fixer/tests/TestCase.php',
            // dependency for "composer dump"
            __DIR__ . '/vendor/composer/installed.json'
        ]),
        // 'whitelist' - be careful, this adds aliases to end of each whitelisted class

        // Fixes non-standard php-cs-fixer tests in /src:
        // "Could not scan for classes inside "/var/www/rector/build/vendor/friendsofphp/php-cs-fixer/tests/Test/AbstractFixerTestCase.php" which does not appear to be a file nor a folder"
        Finder::create()
            ->files()
            ->in(__DIR__ . '/vendor/friendsofphp/php-cs-fixer/tests/Test')
    ],
];
