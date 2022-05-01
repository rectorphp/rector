<?php

declare (strict_types=1);
namespace Rector\CakePHP\Naming;

use RectorPrefix20220501\Nette\Utils\Strings;
use PHPStan\Reflection\ReflectionProvider;
use Rector\CakePHP\ImplicitNameResolver;
use Rector\Core\Util\StringUtils;
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
    private const SLASH_REGEX = '#(/|\\.)#';
    /**
     * @var string
     * @see https://regex101.com/r/lq0lQ9/1
     */
    private const PLUGIN_OR_LIB_REGEX = '#(Plugin|Lib)#';
    /**
     * @readonly
     * @var \Rector\CakePHP\ImplicitNameResolver
     */
    private $implicitNameResolver;
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    public function __construct(\Rector\CakePHP\ImplicitNameResolver $implicitNameResolver, \PHPStan\Reflection\ReflectionProvider $reflectionProvider)
    {
        $this->implicitNameResolver = $implicitNameResolver;
        $this->reflectionProvider = $reflectionProvider;
    }
    /**
     * This value used to be directory So "/" in path should be "\" in namespace
     */
    public function resolveFromPseudoNamespaceAndShortClassName(string $pseudoNamespace, string $shortClass) : string
    {
        $pseudoNamespace = $this->normalizeFileSystemSlashes($pseudoNamespace);
        $resolvedShortClass = $this->implicitNameResolver->resolve($shortClass);
        // A. is known renamed class?
        if ($resolvedShortClass !== null) {
            return $resolvedShortClass;
        }
        // Chop Lib out as locations moves those files to the top level.
        // But only if Lib is not the last folder.
        if (\Rector\Core\Util\StringUtils::isMatch($pseudoNamespace, self::LIB_NAMESPACE_PART_REGEX)) {
            $pseudoNamespace = \RectorPrefix20220501\Nette\Utils\Strings::replace($pseudoNamespace, '#\\\\Lib#', '');
        }
        // B. is Cake native class?
        $cakePhpVersion = 'Cake\\' . $pseudoNamespace . '\\' . $shortClass;
        if ($this->reflectionProvider->hasClass($cakePhpVersion)) {
            return $cakePhpVersion;
        }
        // C. is not plugin nor lib custom App class?
        if (\strpos($pseudoNamespace, '\\') !== \false && !\Rector\Core\Util\StringUtils::isMatch($pseudoNamespace, self::PLUGIN_OR_LIB_REGEX)) {
            return 'App\\' . $pseudoNamespace . '\\' . $shortClass;
        }
        return $pseudoNamespace . '\\' . $shortClass;
    }
    private function normalizeFileSystemSlashes(string $pseudoNamespace) : string
    {
        return \RectorPrefix20220501\Nette\Utils\Strings::replace($pseudoNamespace, self::SLASH_REGEX, '\\');
    }
}
