<?php

declare(strict_types=1);

// this file will need update sometimes: https://github.com/phpstan/phpstan-src/commits/master/compiler/build/scoper.inc.php
// automate in the future, if needed

require __DIR__ . '/../vendor/autoload.php';

use Isolated\Symfony\Component\Finder\Finder;
use Nette\Neon\Neon;
use Symfony\Component\Finder\SplFileInfo;

$stubs = [];

$stubFinder = Finder::create();
$stubFinder->files()->name('*.php')
    ->in(__DIR__ . '/../../stubs')
    ->in(__DIR__ . '/../../vendor/jetbrains/phpstorm-stubs');

foreach ($stubFinder->getIterator() as $fileInfo) {
    /** @var SplFileInfo $fileInfo */
    $stubs[] = $fileInfo->getPathName();
}

return [
    'prefix' => null,
    'finders' => [],
    'files-whitelist' => $stubs,
    'patchers' => [
        function (string $filePath, string $prefix, string $content): string {
            if ($filePath !== 'bin/rector') {
                return $content;
            }
            return str_replace('__DIR__ . \'/..', '\'phar://rector.phar', $content);
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
            $content = str_replace(sprintf('\'%s\\\\callable', $prefix), '\'callable', $content);
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
            $content = str_replace(sprintf('\'%s\\\\string', $prefix), '\'string', $content);
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

        // mimics https://github.com/phpstan/phpstan-src/commit/5a6a22e5c4d38402c8cc888d8732360941c33d43#diff-463a36e4a5687fb2366b5ee56cdad92d
        function (string $filePath, string $prefix, string $content): string {
            if (strpos($filePath, '.neon') === false) {
                return $content;
            }
            if ($content === '') {
                return $content;
            }
            $prefixClass = function (string $class) use ($prefix): string {
                if (strpos($class, 'PHPStan\\') === 0) {
                    return $class;
                }
                if (strpos($class, 'PhpParser\\') === 0) {
                    return $class;
                }
                if (strpos($class, '@') === 0) {
                    return $class;
                }
                return $prefix . '\\' . $class;
            };
            $neon = Neon::decode($content);
            $updatedNeon = $neon;
            if (array_key_exists('services', $neon)) {
                foreach ($neon['services'] as $key => $service) {
                    if (array_key_exists('class', $service) && is_string($service['class'])) {
                        $service['class'] = $prefixClass($service['class']);
                    }
                    if (array_key_exists('factory', $service) && is_string($service['factory'])) {
                        $service['factory'] = $prefixClass($service['factory']);
                    }
                    $updatedNeon['services'][$key] = $service;
                }
            }
            return Neon::encode($updatedNeon, Neon::BLOCK);
        },

        // mimics https://github.com/phpstan/phpstan-src/commit/fd8f0a852207a1724ae4a262f47d9a449de70da4#diff-463a36e4a5687fb2366b5ee56cdad92d
        function (string $filePath, string $prefix, string $content): string {
            if (strpos($filePath, 'src/') !== 0) {
                return $content;
            }
            $content = str_replace(sprintf('\'%s\\\\r\\\\n\'', $prefix), '\'\\\\r\\\\n\'', $content);
            return str_replace(sprintf('\'%s\\\\', $prefix), '\'', $content);
        },
    ],
    'whitelist' => ['Rector\*', 'PHPStan\*', 'PhpParser\*'],
];
