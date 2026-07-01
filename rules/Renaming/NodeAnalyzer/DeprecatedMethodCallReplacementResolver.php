<?php

declare (strict_types=1);
namespace Rector\Renaming\NodeAnalyzer;

use RectorPrefix202607\Nette\Utils\Strings;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MethodReflection;
/**
 * @see \Rector\Tests\Renaming\NodeAnalyzer\DeprecatedMethodCallReplacementResolverTest
 */
final class DeprecatedMethodCallReplacementResolver
{
    /**
     * Matches the new method name suggested inside a "@deprecated" description, e.g.:
     *  - "use newMethod() instead"
     *  - "replaced by newMethod()"
     *  - "{@see newMethod()}"
     *
     * Cross-class suggestions ("Other::newMethod()") are intentionally skipped for now.
     * @var string
     */
    private const RENAME_SUGGESTION_REGEX = '#(?:\buse\b|\breplaced by\b|\{@see)\s+(?<method>\w+)\(\)#i';
    /**
     * Resolves a non-deprecated replacement method name suggested by the "@deprecated" docblock
     * of the given method, or null when there is no usable suggestion.
     */
    public function resolve(MethodReflection $methodReflection): ?string
    {
        if (!$methodReflection->isDeprecated()->yes()) {
            return null;
        }
        $newMethodName = $this->matchNewMethodName($methodReflection->getDeprecatedDescription());
        if ($newMethodName === null) {
            return null;
        }
        // already the suggested name? nothing to do
        if (strtolower($methodReflection->getName()) === strtolower($newMethodName)) {
            return null;
        }
        if (!$this->isExistingNonDeprecatedMethod($methodReflection->getDeclaringClass(), $newMethodName)) {
            return null;
        }
        return $newMethodName;
    }
    private function matchNewMethodName(?string $deprecatedDescription): ?string
    {
        if ($deprecatedDescription === null || $deprecatedDescription === '') {
            return null;
        }
        $match = Strings::match($deprecatedDescription, self::RENAME_SUGGESTION_REGEX);
        if ($match === null) {
            return null;
        }
        return $match['method'];
    }
    private function isExistingNonDeprecatedMethod(ClassReflection $classReflection, string $newMethodName): bool
    {
        if (!$classReflection->hasNativeMethod($newMethodName)) {
            return \false;
        }
        // do not rename onto another deprecated method, to avoid suggesting a dead end
        $extendedMethodReflection = $classReflection->getNativeMethod($newMethodName);
        return !$extendedMethodReflection->isDeprecated()->yes();
    }
}
