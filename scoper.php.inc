<?php

declare(strict_types=1);

use Nette\Utils\DateTime;
use Nette\Utils\Strings;
use Rector\Compiler\PhpScoper\StaticEasyPrefixer;
use Rector\Compiler\ValueObject\ScoperOption;
use Symfony\Component\Finder\Finder;

require_once __DIR__ . '/vendor/autoload.php';

// [BEWARE] this path is relative to the root and location of this file
$filePathsToSkip = [
    // @see https://github.com/rectorphp/rector/issues/2852#issuecomment-586315588
    'vendor/symfony/deprecation-contracts/function.php'
];

// remove phpstan, because it is already prefixed in its own scope
$finder = new Finder();
$phpstanPhpFileInfos = $finder->files()
    ->name('*.php')
    // the working dir is already "rector-nested"
    ->in(__DIR__ . '/vendor/phpstan/phpstan-extracted')
    ->getIterator();

foreach ($phpstanPhpFileInfos as $phpstanPhpFileInfo) {
    $filePathsToSkip[] = $phpstanPhpFileInfo->getRealPath();
}

$dateTime = DateTime::from('now');
$timestamp = $dateTime->format('YMD');

// see https://github.com/humbug/php-scoper
return [
    ScoperOption::PREFIX => 'RectorPrefix' . $timestamp,
    ScoperOption::FILES_WHITELIST => $filePathsToSkip,
    ScoperOption::WHITELIST => StaticEasyPrefixer::getExcludedNamespacesAndClasses(),
    ScoperOption::PATCHERS => [
        // [BEWARE] $filePath is absolute!

        // related to Composer 2 naming - @todo why exactly is this needed?
        function (string $filePath, string $prefix, string $content): string {
            if (! Strings::endsWith($filePath, 'vendor/composer/autoload_real.php')) {
                return $content;
            }

            $content = str_replace(
                "'Composer\\\\Autoload\\\\ClassLoader",
                "'" . $prefix . '\\\\Composer\\\\Autoload\\\\ClassLoader',
                $content
            );

            return $content;
        },

        // fatal error on PHP 8
        function (string $filePath, string $prefix, string $content): string {
            return str_replace('private static final', 'private static', $content);
        },
    ],
];
