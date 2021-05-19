<?php

declare(strict_types=1);

use Nette\Utils\DateTime;
use Nette\Utils\Strings;
use Rector\Compiler\PhpScoper\StaticEasyPrefixer;
use Rector\Compiler\Unprefixer;
use Rector\Compiler\ValueObject\ScoperOption;

require_once __DIR__ . '/vendor/autoload.php';

// [BEWARE] this path is relative to the root and location of this file
$filePathsToRemoveNamespace = [
    // @see https://github.com/rectorphp/rector/issues/2852#issuecomment-586315588
    'vendor/symfony/deprecation-contracts/function.php',
    // it would make polyfill function work only with namespace = brokes
    'vendor/symfony/polyfill-ctype/bootstrap.php',
    'vendor/symfony/polyfill-intl-normalizer/bootstrap.php',
    'vendor/symfony/polyfill-intl-grapheme/bootstrap.php',
    'vendor/symfony/polyfill-mbstring/bootstrap.php',
    'vendor/symfony/polyfill-php80/bootstrap.php',
    'vendor/symfony/polyfill-php74/bootstrap.php',
    'vendor/symfony/polyfill-php73/bootstrap.php',
    'vendor/symfony/polyfill-php72/bootstrap.php',
    'vendor/symfony/polyfill-uuid/bootstrap.php',
];

// remove phpstan, because it is already prefixed in its own scope

$dateTime = DateTime::from('now');
$timestamp = $dateTime->format('Ymd');

// see https://github.com/humbug/php-scoper
return [
    ScoperOption::PREFIX => 'RectorPrefix' . $timestamp,
    ScoperOption::WHITELIST => StaticEasyPrefixer::getExcludedNamespacesAndClasses(),
    ScoperOption::PATCHERS => [
        // [BEWARE] $filePath is absolute!

        // fixes https://github.com/rectorphp/rector-prefixed/runs/2143717534
        function (string $filePath, string $prefix, string $content) use ($filePathsToRemoveNamespace): string {
            // @see https://regex101.com/r/0jaVB1/1
            $prefixedNamespacePattern = '#^namespace (.*?);$#m';

            foreach ($filePathsToRemoveNamespace as $filePathToRemoveNamespace) {
                if (Strings::endsWith($filePath, $filePathToRemoveNamespace)) {
                    return Strings::replace($content, $prefixedNamespacePattern, '');
                }
            }

            return $content;
        },

        // unprefixed SmartFileInfo
        function (string $filePath, string $prefix, string $content): string {
            return Strings::replace(
                $content, '
                #' . $prefix . '\\\\Symplify\\\\SmartFileSystem\\\\SmartFileInfo#',
                'Symplify\SmartFileSystem\SmartFileInfo'
            );
        },

        function (string $filePath, string $prefix, string $content): string {
            if (! Strings::contains($filePath, 'vendor/')) {
                return $content;
            }

            // see https://regex101.com/r/PDGN3K/1
            $content = Strings::replace(
                $content, '
                #: \?\\\\.*#',
                ''
            );

            // see https://regex101.com/r/P7nbpU/1
            $content = Strings::replace(
                $content, '
                #\(\?\\.*\s+#',
                '('
            );

            return $content;
        },

        // get version for prefixed version
        function (string $filePath, string $prefix, string $content): string {
            if (! Strings::endsWith($filePath, 'src/Configuration/Configuration.php')) {
                return $content;
            }

            // @see https://regex101.com/r/gLefQk/1
            return Strings::replace(
                $content, '#\(\'rector\/rector\'\)#',
                "('rector/rector-prefixed')"
            );
        },

        // un-prefix composer plugin
        function (string $filePath, string $prefix, string $content): string {
            if (! Strings::endsWith($filePath, 'vendor/rector/extension-installer/src/Plugin.php')) {
                return $content;
            }

            // see https://regex101.com/r/v8zRMm/1
            return Strings::replace($content, '#' . $prefix . '\\\\Composer\\\\#', 'Composer\\');
        },

        // fixes https://github.com/rectorphp/rector/issues/6007
        function (string $filePath, string $prefix, string $content): string {
            if (! Strings::contains($filePath, 'vendor/')) {
                return $content;
            }

            // @see https://regex101.com/r/lBV8IO/2
            $fqcnReservedPattern = sprintf('#(\\\\)?%s\\\\(parent|self|static)#m', $prefix);
            $matches             = Strings::matchAll($content, $fqcnReservedPattern);

            if (! $matches) {
                return $content;
            }

            foreach ($matches as $match) {
                $content = str_replace($match[0], $match[2], $content);
            }

            return $content;
        },


        function (string $filePath, string $prefix, string $content): string {
            if (
                ! Strings::endsWith($filePath, 'packages/Testing/PHPUnit/AbstractTestCase.php')
                && ! Strings::endsWith($filePath, 'packages/Testing/PHPUnit/AbstractRectorTestCase.php')
            ) {
                return $content;
            }

            // un-prefix
            return Strings::replace(
                $content,
                '#' . $prefix . '\\\\PHPUnit\\\\Framework\\\\TestCase#',
                'PHPUnit\Framework\TestCase'
            );
        },

        // fixes https://github.com/rectorphp/rector/issues/6010 + test case prefix
        function (string $filePath, string $prefix, string $content): string {
            // @see https://regex101.com/r/bA1nQa/1
            if (! Strings::match($filePath, '#vendor/symfony/polyfill-php\d{2}/Resources/stubs#')) {
                return $content;
            }

            // @see https://regex101.com/r/x5Ukrx/1
            $namespace = sprintf('#namespace %s;#m', $prefix);
            return Strings::replace($content, $namespace);
        },

        function (string $filePath, string $prefix, string $content): string {
            if (! Strings::contains($filePath, 'vendor/')) {
                return $content;
            }

            // @see https://regex101.com/r/wcAbLf/1
            return Strings::replace(
                $content, '#((private|public|protected)\s+const)#',
                "const"
            );
        },

        function (string $filePath, string $prefix, string $content): string {
            if (! Strings::contains($filePath, 'vendor/')) {
                return $content;
            }

            // @see https://regex101.com/r/r3AJFl/1
            return Strings::replace(
                $content, '#\)\s{0,}:\s{0,}void#',
                ")"
            );
        },

        // unprefix string classes, as they're string on purpose - they have to be checked in original form, not prefixed
        function (string $filePath, string $prefix, string $content): string {
            // skip vendor, expect rector packages
            if (Strings::contains($filePath, 'vendor/') && ! Strings::contains($filePath, 'vendor/rector') && ! Strings::contains($filePath, 'vendor/ssch/typo3-rector')) {
                return $content;
            }

            // skip bin/rector.php for composer autoload class
            if (Strings::endsWith($filePath, 'bin/rector.php')) {
                return $content;
            }

            return Unprefixer::unprefixQuoted($content, $prefix);
        },

        // scoper missed PSR-4 autodiscovery in Symfony
        function (string $filePath, string $prefix, string $content): string {
            // scoper missed PSR-4 autodiscovery in Symfony
            if (! Strings::endsWith($filePath, 'config.php') && ! Strings::endsWith($filePath, 'services.php')) {
                return $content;
            }

            // skip "Rector\\" namespace
            if (Strings::contains($content, '$services->load(\'Rector')) {
                return $content;
            }

            return Strings::replace($content, '#services\->load\(\'#', 'services->load(\'' . $prefix . '\\');
        },
    ],
];
