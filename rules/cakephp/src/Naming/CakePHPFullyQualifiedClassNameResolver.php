<?php

declare(strict_types=1);

namespace Rector\CakePHP\Naming;

use Nette\Utils\Strings;
use Rector\CakePHP\ImplicitNameResolver;

/**
 * @inspired https://github.com/cakephp/upgrade/blob/756410c8b7d5aff9daec3fa1fe750a3858d422ac/src/Shell/Task/AppUsesTask.php
 */
final class CakePHPFullyQualifiedClassNameResolver
{
    /**
     * @var string
     * @see https://regex101.com/r/mbvKJp/1
     */
    public const LIB_NAMESPACE_PART_REGEX = '#\\\\Lib\\\\#';

    /**
     * @var string
     * @see https://regex101.com/r/XvoZIP/1
     */
    private const SLASH_REGEX = '#(/|\.)#';

    /**
     * @var string
     * @see https://regex101.com/r/lq0lQ9/1
     */
    private const PLUGIN_OR_LIB_REGEX = '#(Plugin|Lib)#';

    /**
     * @var ImplicitNameResolver
     */
    private $implicitNameResolver;

    public function __construct(ImplicitNameResolver $implicitNameResolver)
    {
        $this->implicitNameResolver = $implicitNameResolver;
    }

    /**
     * This value used to be directory
     * So "/" in path should be "\" in namespace
     */
    public function resolveFromPseudoNamespaceAndShortClassName(string $pseudoNamespace, string $shortClass): string
    {
        $pseudoNamespace = $this->normalizeFileSystemSlashes($pseudoNamespace);

        $resolvedShortClass = $this->implicitNameResolver->resolve($shortClass);

        // A. is known renamed class?
        if ($resolvedShortClass !== null) {
            return $resolvedShortClass;
        }

        // Chop Lib out as locations moves those files to the top level.
        // But only if Lib is not the last folder.
        if (Strings::match($pseudoNamespace, self::LIB_NAMESPACE_PART_REGEX)) {
            $pseudoNamespace = Strings::replace($pseudoNamespace, '#\\\\Lib#', '');
        }

        // B. is Cake native class?
        $cakePhpVersion = 'Cake\\' . $pseudoNamespace . '\\' . $shortClass;
        if (class_exists($cakePhpVersion)) {
            return $cakePhpVersion;
        }
        if (interface_exists($cakePhpVersion)) {
            return $cakePhpVersion;
        }

        // C. is not plugin nor lib custom App class?
        if (Strings::contains($pseudoNamespace, '\\') && ! Strings::match(
            $pseudoNamespace,
            self::PLUGIN_OR_LIB_REGEX
        )) {
            return 'App\\' . $pseudoNamespace . '\\' . $shortClass;
        }

        return $pseudoNamespace . '\\' . $shortClass;
    }

    private function normalizeFileSystemSlashes(string $pseudoNamespace): string
    {
        return Strings::replace($pseudoNamespace, self::SLASH_REGEX, '\\');
    }
}
