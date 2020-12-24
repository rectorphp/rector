<?php

declare(strict_types=1);

use Nette\Utils\Strings;
use Rector\Compiler\PhpScoper\StaticEasyPrefixer;
use Rector\Compiler\ValueObject\ScoperOption;
use Symfony\Component\Finder\Finder;

$vendorAutoloadPath = __DIR__ . '/vendor/autoload.php';
$vendorAutoloadPath2 = __DIR__ . '/../vendor/autoload.php';

var_dump($vendorAutoloadPath);
var_dump(file_exists($vendorAutoloadPath));

var_dump($vendorAutoloadPath2);
var_dump(file_exists($vendorAutoloadPath2));

require $vendorAutoloadPath;

// [BEWARE] this path is relative to the root and location of this file
$filePathsToSkip = [
    // @see https://github.com/rectorphp/rector/issues/2852#issuecomment-586315588
    'vendor/symfony/deprecation-contracts/function.php'
];

// remove phpstan, because it is already prefixed in its own scope
$finder = new Finder();
$phpstanPhpFileInfos = $finder->files()
    ->name('*.php')
    ->in(__DIR__ . '/rector-nested/vendor/phpstan/phpstan-extracted')
    ->getIterator();

foreach ($phpstanPhpFileInfos as $phpstanPhpFileInfo) {
    $filePathsToSkip[] = $phpstanPhpFileInfo->getRealPath();
}


// see https://github.com/humbug/php-scoper
return [
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

        // un-prefix excluded classes
        // fixes https://github.com/humbug/box/issues/470
        function (string $filePath, string $prefix, string $content): string {
            foreach (StaticEasyPrefixer::EXCLUDED_CLASSES as $excludedClass) {
                $prefixedClassPattern = '#' . $prefix . '\\\\' . preg_quote($excludedClass, '#') . '#';
                $content = Strings::replace($content, $prefixedClassPattern, $excludedClass);
            }

            return $content;
        },

        // fatal error on PHP 8
        function (string $filePath, string $prefix, string $content): string {
            return str_replace('private static final', 'private static', $content);
        },
    ],
];
