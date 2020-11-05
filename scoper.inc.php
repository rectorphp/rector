<?php

declare(strict_types=1);

// this file will need update sometimes: https://github.com/phpstan/phpstan-src/commits/master/compiler/build/scoper.inc.php
// automate in the future, if needed - @see https://github.com/rectorphp/rector/pull/2575#issuecomment-571133000

require_once __DIR__ . '/vendor/autoload.php';

use Nette\Neon\Neon;
use Nette\Utils\Strings;
use Rector\Compiler\PhpScoper\StaticEasyPrefixer;
use Rector\Compiler\PhpScoper\WhitelistedStubsProvider;

require_once __DIR__ . '/utils/compiler/src/PhpScoper/StaticEasyPrefixer.php';
require_once __DIR__ . '/utils/compiler/src/PhpScoper/WhitelistedStubsProvider.php';

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
        function (string $filePath, string $prefix, string $content): string {
            if (! Strings::endsWith($filePath, 'vendor/nette/di/src/DI/Compiler.php')) {
                return $content;
            }

            return str_replace(
                '|Nette\\\\DI\\\\Statement',
                sprintf('|\\\\%s\\\\Nette\\\\DI\\\\Statement', $prefix),
                $content
            );
        },
        function (string $filePath, string $prefix, string $content): string {
            if (! Strings::endsWith($filePath, 'vendor/nette/di/src/DI/Config/DefinitionSchema.php')) {
                return $content;
            }

            $content = str_replace(sprintf('\'%s\\\\callable', $prefix), "'callable", $content);

            return str_replace(
                '|Nette\\\\DI\\\\Definitions\\\\Statement',
                sprintf('|%s\\\\Nette\\\\DI\\\\Definitions\\\\Statement', $prefix),
                $content
            );
        },
        function (string $filePath, string $prefix, string $content): string {
            if (! Strings::endsWith($filePath, 'vendor/nette/di/src/DI/Extensions/ExtensionsExtension.php')) {
                return $content;
            }

            $content = str_replace(sprintf('\'%s\\\\string', $prefix), "'string", $content);
            return str_replace(
                '|Nette\\\\DI\\\\Definitions\\\\Statement',
                sprintf('|%s\\\\Nette\\\\DI\\\\Definitions\\\\Statement', $prefix),
                $content
            );
        },
        function (string $filePath, string $prefix, string $content): string {
            if (! Strings::endsWith($filePath, 'vendor/phpstan/phpstan-src/src/Testing/TestCase.php')) {
                return $content;
            }

            return str_replace(
                sprintf('\\%s\\PHPUnit\\Framework\\TestCase', $prefix),
                '\\PHPUnit\\Framework\\TestCase',
                $content
            );
        },
        function (string $filePath, string $prefix, string $content): string {
            if (! Strings::endsWith($filePath, 'vendor/phpstan/phpstan-src/src/Testing/LevelsTestCase.php')) {
                return $content;
            }

            return str_replace(
                [
                    sprintf('\\%s\\PHPUnit\\Framework\\AssertionFailedError', $prefix),
                    sprintf('\\%s\\PHPUnit\\Framework\\TestCase', $prefix),
                ],
                ['\\PHPUnit\\Framework\\AssertionFailedError', '\\PHPUnit\\Framework\\TestCase'],
                $content
            );
        },

        // unprefix excluded classes
        // fixes https://github.com/humbug/box/issues/470
        function (string $filePath, string $prefix, string $content): string {
            foreach (StaticEasyPrefixer::EXCLUDED_CLASSES as $excludedClass) {
                $prefixedClassPattern = '#' . $prefix . '\\\\' . preg_quote($excludedClass, '#') . '#';
                $content = Strings::replace($content, $prefixedClassPattern, $excludedClass);
            }

            return $content;
        },

        // mimics https://github.com/phpstan/phpstan-src/commit/5a6a22e5c4d38402c8cc888d8732360941c33d43#diff-463a36e4a5687fb2366b5ee56cdad92d
        function (string $filePath, string $prefix, string $content): string {
            // only *.neon files
            if (! Strings::endsWith($filePath, '.neon')) {
                return $content;
            }

            if ($content === '') {
                return $content;
            }

            $neon = Neon::decode($content);
            $updatedNeon = $neon;

            if (array_key_exists('services', $neon)) {
                foreach ($neon['services'] as $key => $service) {
                    if (array_key_exists('class', $service) && is_string($service['class'])) {
                        $service['class'] = StaticEasyPrefixer::prefixClass($service['class'], $prefix);
                    }

                    if (array_key_exists('factory', $service) && is_string($service['factory'])) {
                        $service['factory'] = StaticEasyPrefixer::prefixClass($service['factory'], $prefix);
                    }

                    if (array_key_exists('autowired', $service) && is_array($service['autowired'])) {
                        foreach ($service['autowired'] as $i => $autowiredName) {
                            $service['autowired'][$i] = StaticEasyPrefixer::prefixClass($autowiredName, $prefix);
                        }
                    }

                    $updatedNeon['services'][$key] = $service;
                }
            }

            $updatedContent = Neon::encode($updatedNeon, Neon::BLOCK);

            // default indent is tab, we have spaces
            return Strings::replace($updatedContent, '#\t#', '    ');
        },

        // mimics https://github.com/phpstan/phpstan-src/commit/fd8f0a852207a1724ae4a262f47d9a449de70da4#diff-463a36e4a5687fb2366b5ee56cdad92d
        function (string $filePath, string $prefix, string $content): string {
            if (! Strings::match($filePath, '#phpstan-src\/(config|src|rules|packages)\/#')) {
                return $content;
            }

            $content = StaticEasyPrefixer::unPrefixQuotedValues($prefix, $content);

            return StaticEasyPrefixer::unPreSlashQuotedValues($content);
        },

        // mimics
        // https://github.com/phpstan/phpstan-src/commit/9c2eb91b630bdfee2c1bb642a4c81ebfa0f1ca9a#diff-87f75ce3f908a819a9a2c77ffeffcc38
        // https://github.com/phpstan/phpstan-src/commit/7048109ab17aa16102dc0fd21190782e6d6d5e7e#diff-87f75ce3f908a819a9a2c77ffeffcc38
        function (string $filePath, string $prefix, string $content): string {
            if (! in_array($filePath, [
                'vendor/phpstan/phpstan-src/src/Type/TypehintHelper.php',
                'vendor/ondrejmirtes/better-reflection/src/Reflection/Adapter/ReflectionUnionType.php',
            ], true)) {
                return $content;
            }

            return str_replace(sprintf('%s\\ReflectionUnionType', $prefix), 'ReflectionUnionType', $content);
        },

        // mimics: https://github.com/phpstan/phpstan-src/commit/6bb92ed7b92b186bb1eb5111bc49ec7679ed780f#diff-87f75ce3f908a819a9a2c77ffeffcc38
        function (string $filePath, string $prefix, string $content): string {
            return str_replace('private static final', 'private static', $content);
        },

        // mimics: https://github.com/phpstan/phpstan-src/commit/1c63a785e5fce8d031b04f52c61904bd57b51e27#diff-87f75ce3f908a819a9a2c77ffeffcc38
        function (string $filePath, string $prefix, string $content): string {
            if (! in_array($filePath, [
                'vendor/phpstan/phpstan-src/src/Testing/TestCaseSourceLocatorFactory.php',
                'vendor/phpstan/phpstan-src/src/Testing/TestCase.php',
            ], true)) {
                return $content;
            }

            return str_replace(
                sprintf('%s\\Composer\\Autoload\\ClassLoader', $prefix),
                'Composer\\Autoload\\ClassLoader',
                $content
            );
        },
    ],
];
