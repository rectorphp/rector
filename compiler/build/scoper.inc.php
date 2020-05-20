<?php

declare(strict_types=1);

// this file will need update sometimes: https://github.com/phpstan/phpstan-src/commits/master/compiler/build/scoper.inc.php
// automate in the future, if needed - @see https://github.com/rectorphp/rector/pull/2575#issuecomment-571133000

require_once __DIR__ . '/../vendor/autoload.php';

use Isolated\Symfony\Component\Finder\Finder;
use Nette\Neon\Neon;

final class WhitelistedStubsProvider
{
    /**
     * @return string[]
     */
    public function provide(): array
    {
        $stubs = [
            // @see https://github.com/rectorphp/rector/issues/2852#issuecomment-586315588
            '../../vendor/hoa/consistency/Prelude.php',
        ];

        // mirrors https://github.com/phpstan/phpstan-src/commit/04f777bc4445725d17dac65c989400485454b145
        $stubsDirectory = __DIR__ . '/../../vendor/jetbrains/phpstorm-stubs';
        if (file_exists($stubsDirectory)) {
            $stubFinder = Finder::create()
                ->files()
                ->name('*.php')
                ->in($stubsDirectory)
                ->notName('#PhpStormStubsMap\.php$#');

            foreach ($stubFinder->getIterator() as $fileInfo) {
                /** @var SplFileInfo $fileInfo */
                $stubs[] = $fileInfo->getPathName();
            }
        }

        return $stubs;
    }
}

$whitelistedStubsProvider = new WhitelistedStubsProvider();

final class EasyPrefixer
{
    /**
     * @var string[]
     */
    public const ALLOWED_PREFIXES = ['Hoa\*', 'PhpParser\*', 'PHPStan\*', 'Rector\*'];


    public static function prefixClass(string $class, string $prefix): string
    {
        // @todo move to allowed prefixes
        if (strpos($class, 'PHPStan\\') === 0) {
            return $class;
        }
        if (strpos($class, 'PhpParser\\') === 0) {
            return $class;
        }
        if (strpos($class, 'Rector\\') === 0) {
            return $class;
        }
        if (strpos($class, 'Hoa\\') === 0) {
            return $class;
        }
        if (strpos($class, '@') === 0) {
            return $class;
        }
        return $prefix . '\\' . $class;
    }
}

return [
    'prefix' => null,
    'finders' => [],
    'files-whitelist' => $whitelistedStubsProvider->provide(),
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

        function (string $filePath, string $prefix, string $content): string {
            // only *.yaml files
            if (strpos($filePath, '.yaml') === false) {
                return $content;
            }

            // @see https://github.com/rectorphp/rector/issues/3227
            if (strpos($filePath, 'config/set/') !== 0) {
                return $content;
            }

            // @todo - prefix classes in yaml files?
            return $content;
        },

        // mimics https://github.com/phpstan/phpstan-src/commit/5a6a22e5c4d38402c8cc888d8732360941c33d43#diff-463a36e4a5687fb2366b5ee56cdad92d
        function (string $filePath, string $prefix, string $content): string {
            // only *.neon files
            if (strpos($filePath, '.neon') === false) {
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
                        $service['class'] = EasyPrefixer::prefixClass($service['class'], $prefix);
                    }

                    if (array_key_exists('factory', $service) && is_string($service['factory'])) {
                        $service['factory'] = EasyPrefixer::prefixClass($service['factory'], $prefix);
                    }

                    if (array_key_exists('autowired', $service) && is_array($service['autowired'])) {
                        foreach ($service['autowired'] as $i => $autowiredName) {
                            $service['autowired'][$i] = EasyPrefixer::prefixClass($autowiredName, $prefix);
                        }
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
    'whitelist' => EasyPrefixer::ALLOWED_PREFIXES,
];
