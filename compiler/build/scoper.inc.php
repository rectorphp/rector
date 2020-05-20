<?php

declare(strict_types=1);

// this file will need update sometimes: https://github.com/phpstan/phpstan-src/commits/master/compiler/build/scoper.inc.php
// automate in the future, if needed - @see https://github.com/rectorphp/rector/pull/2575#issuecomment-571133000

require_once __DIR__ . '/../vendor/autoload.php';

use Nette\Neon\Neon;
use Nette\Utils\Strings;
use Rector\Compiler\PhpScoper\StaticEasyPrefixer;
use Rector\Compiler\PhpScoper\WhitelistedStubsProvider;

$whitelistedStubsProvider = new WhitelistedStubsProvider();

return [
    'prefix' => null,
    'finders' => [],
    'files-whitelist' => $whitelistedStubsProvider->provide(),
    'patchers' => [
        function (string $filePath, string $prefix, string $content): string {
            if ($filePath !== 'bin/rector') {
                return $content;
            }

            return str_replace("__DIR__ . '/..", "'phar://rector.phar", $content);
        },
        function (string $filePath, string $prefix, string $content): string {
            if ($filePath !== 'vendor/nette/di/src/DI/Compiler.php') {
                return $content;
            }

            return str_replace(
                '|Nette\\\\DI\\\\Statement',
                sprintf('|\\\\%s\\\\Nette\\\\DI\\\\Statement', $prefix),
                $content
            );
        },
        function (string $filePath, string $prefix, string $content): string {
            if ($filePath !== 'vendor/nette/di/src/DI/Config/DefinitionSchema.php') {
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
            if ($filePath !== 'vendor/nette/di/src/DI/Extensions/ExtensionsExtension.php') {
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
            if ($filePath !== 'src/Testing/TestCase.php') {
                return $content;
            }

            return str_replace(
                sprintf('\\%s\\PHPUnit\\Framework\\TestCase', $prefix),
                '\\PHPUnit\\Framework\\TestCase',
                $content
            );
        },
        function (string $filePath, string $prefix, string $content): string {
            if ($filePath !== 'src/Testing/LevelsTestCase.php') {
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

        // unprefix configuraion in sets, @see https://github.com/rectorphp/rector/issues/3227
        function (string $filePath, string $prefix, string $content): string {
            // only *.yaml files
            if (! Strings::endsWith($filePath, '.yaml')) {
                return $content;
            }

            if (! Strings::startsWith($filePath, 'config/set/')) {
                return $content;
            }

            return StaticEasyPrefixer::unprefixQuotedValues($prefix, $content);
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
            if (! Strings::contains($filePath, 'src/')) {
                return $content;
            }

            if (Strings::startsWith($filePath, 'src/')) {
                return $content;
            }

            return StaticEasyPrefixer::unprefixQuotedValues($prefix, $content);
        },
    ],
    'whitelist' => StaticEasyPrefixer::EXCLUDED_NAMESPACES,
];
