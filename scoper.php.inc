<?php

declare(strict_types=1);

// automate in the future, if needed - @see https://github.com/rectorphp/rector/pull/2575#issuecomment-571133000

require_once __DIR__ . '/vendor/autoload.php';

use Nette\Utils\Strings;
use Rector\Compiler\PhpScoper\StaticEasyPrefixer;
use Rector\Compiler\PhpScoper\WhitelistedStubsProvider;

$whitelistedStubsProvider = new WhitelistedStubsProvider();

// see https://github.com/humbug/php-scoper

return [
    'files-whitelist' => $whitelistedStubsProvider->provide(),
    'whitelist' => StaticEasyPrefixer::getExcludedNamespacesAndClasses(),
    'patchers' => [
        // [BEWARE] $filePath is absolute!
        // related to Composer 2 naming
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

        // unpprefix excluded classes
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
