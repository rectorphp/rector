<?php

declare(strict_types=1);

use Nette\Utils\DateTime;
use Nette\Utils\Strings;
use Rector\Compiler\Unprefixer;
use Rector\Core\Application\VersionResolver;

require_once __DIR__ . '/vendor/autoload.php';

// remove phpstan, because it is already prefixed in its own scope
$dateTime = DateTime::from('now');
$timestamp = $dateTime->format('Ymd');

// @see https://github.com/humbug/php-scoper/blob/master/docs/further-reading.md
use Isolated\Symfony\Component\Finder\Finder;

$polyfillsBootstraps = array_map(
    static fn (SplFileInfo $fileInfo) => $fileInfo->getPathname(),
    iterator_to_array(
        Finder::create()
            ->files()
            ->in(__DIR__ . '/vendor/symfony/polyfill-*')
            ->name('bootstrap*.php'),
        false,
    ),
);

$polyfillsStubs = array_map(
    static fn (SplFileInfo $fileInfo) => $fileInfo->getPathname(),
    iterator_to_array(
        Finder::create()
            ->files()
            ->in(__DIR__ . '/vendor/symfony/polyfill-*/Resources/stubs')
            ->name('*.php'),
        false,
    ),
);

// see https://github.com/humbug/php-scoper/blob/master/docs/configuration.md#configuration
return [
    'prefix' => 'RectorPrefix' . $timestamp,

    // exclude
    'exclude-classes' => [
        'PHPUnit\Framework\Constraint\IsEqual',
        'PHPUnit\Framework\TestCase',
        'PHPUnit\Framework\ExpectationFailedException',
    ],
    'exclude-namespaces' => [
        '#^Rector#',
        '#^PhpParser#',
        '#^PHPStan#',
        '#^Symplify\\\\RuleDocGenerator#',
        '#^Symfony\\\\Polyfill#',
    ],
    'exclude-files' => [
        ...$polyfillsBootstraps,
        ...$polyfillsStubs,
        'vendor/symfony/deprecation-contracts/function.php',
    ],

    // expose
    'expose-classes' => [
        'Normalizer',
        // used by public API
        'Symplify\SmartFileSystem\SmartFileInfo',
        'Symplify\ComposerJsonManipulator\ValueObject\ComposerJson',
        'Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator',
    ],
    'expose-functions' => ['u', 'b', 's', 'trigger_deprecation'],
    'expose-constants' => ['__RECTOR_RUNNING__', '#^SYMFONY\_[\p{L}_]+$#',],

    'patchers' => [
        // fix short import bug, @see https://github.com/rectorphp/rector-scoper-017/blob/23f3256a6f5a18483d6eb4659d69ba117501e2e3/vendor/nikic/php-parser/lib/PhpParser/Builder/Declaration.php#L6
        function (string $filePath, string $prefix, string $content): string {
            return str_replace(sprintf('use %s\PhpParser;', $prefix), 'use PhpParser;', $content);
        },

        function (string $filePath, string $prefix, string $content): string {
            if (! \str_ends_with($filePath, 'src/Application/VersionResolver.php')) {
                return $content;
            }

            $releaseDateTime = VersionResolver::resolverReleaseDateTime();

            return strtr(
                $content,
                [
                    '@package_version@' => VersionResolver::resolvePackageVersion(),
                    '@release_date@' => $releaseDateTime->format('Y-m-d H:i:s'),
                ]
            );
        },

        // fixes https://github.com/rectorphp/rector/issues/7017
        function (string $filePath, string $prefix, string $content): string {
            if (str_ends_with($filePath, 'vendor/symfony/string/ByteString.php')) {
                return Strings::replace($content, '#' . $prefix . '\\\\\\\\1_\\\\\\\\2#', '\\\\1_\\\\2');
            }

            if (str_ends_with($filePath, 'vendor/symfony/string/AbstractUnicodeString.php')) {
                return Strings::replace($content, '#' . $prefix . '\\\\\\\\1_\\\\\\\\2#', '\\\\1_\\\\2');
            }

            return $content;
        },

        // un-prefix composer plugin
        function (string $filePath, string $prefix, string $content): string {
            if (! \str_ends_with($filePath, 'vendor/rector/extension-installer/src/Plugin.php')) {
                return $content;
            }

            // see https://regex101.com/r/v8zRMm/1
            return Strings::replace($content, '#' . $prefix . '\\\\Composer\\\\#', 'Composer\\');
        },

        // unprefix string classes, as they're string on purpose - they have to be checked in original form, not prefixed
        function (string $filePath, string $prefix, string $content): string {
            // skip vendor, expect rector packages
            if (\str_contains($filePath, 'vendor/') && ! \str_contains($filePath, 'vendor/rector')) {
                return $content;
            }

            // skip bin/rector.php for composer autoload class
            if (\str_ends_with($filePath, 'bin/rector.php')) {
                return $content;
            }

            return Unprefixer::unprefixQuoted($content, $prefix);
        },

        // scoper missed PSR-4 autodiscovery in Symfony
        function (string $filePath, string $prefix, string $content): string {
            // scoper missed PSR-4 autodiscovery in Symfony
            if (! \str_ends_with($filePath, 'config.php') && ! \str_ends_with($filePath, 'services.php')) {
                return $content;
            }

            // skip "Rector\\" namespace
            if (\str_contains($content, '$services->load(\'Rector')) {
                return $content;
            }

            return Strings::replace($content, '#services\->load\(\'#', "services->load('" . $prefix . '\\');
        },
    ],
];
