<?php

declare(strict_types=1);

namespace Rector\CakePHP;

use Nette\Utils\Strings;

/**
 * @inspired https://github.com/cakephp/upgrade/blob/756410c8b7d5aff9daec3fa1fe750a3858d422ac/src/Shell/Task/AppUsesTask.php
 */
final class FullyQualifiedClassNameResolver
{
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
        if (Strings::match($pseudoNamespace, '#\\\\Lib\\\\#')) {
            $pseudoNamespace = Strings::replace($pseudoNamespace, '#\\\\Lib#');
        }

        // B. is Cake native class?
        $cakePhpVersion = 'Cake\\' . $pseudoNamespace . '\\' . $shortClass;
        if (class_exists($cakePhpVersion) || interface_exists($cakePhpVersion)) {
            return $cakePhpVersion;
        }

        // C. is not plugin nor lib custom App class?
        if (Strings::contains($pseudoNamespace, '\\') && ! Strings::match($pseudoNamespace, '#(Plugin|Lib)#')) {
            return 'App\\' . $pseudoNamespace . '\\' . $shortClass;
        }

        return $pseudoNamespace . '\\' . $shortClass;
    }

    private function normalizeFileSystemSlashes(string $pseudoNamespace): string
    {
        return Strings::replace($pseudoNamespace, '#(/|\.)#', '\\');
    }
}
