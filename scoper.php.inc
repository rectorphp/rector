<?php

declare(strict_types=1);

// automate in the future, if needed - @see https://github.com/rectorphp/rector/pull/2575#issuecomment-571133000

require_once __DIR__ . '/vendor/autoload.php';

use Nette\Utils\Strings;
use Rector\Compiler\PhpScoper\StaticEasyPrefixer;
use Rector\Compiler\ValueObject\ScoperOption;


$filePathsToSkip = [
    // @see https://github.com/rectorphp/rector/issues/2852#issuecomment-586315588
    'vendor/symfony/deprecation-contracts/function.php'
];

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
