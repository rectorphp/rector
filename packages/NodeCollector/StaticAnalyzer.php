<?php

declare (strict_types=1);
namespace Rector\NodeCollector;

use PHPStan\PhpDoc\ResolvedPhpDocBlock;
use PHPStan\Reflection\ClassReflection;
use Rector\Core\Util\StringUtils;
final class StaticAnalyzer
{
    public function isStaticMethod(ClassReflection $classReflection, string $methodName) : bool
    {
        if ($classReflection->hasNativeMethod($methodName)) {
            $extendedMethodReflection = $classReflection->getNativeMethod($methodName);
            if ($extendedMethodReflection->isStatic()) {
                return \true;
            }
        }
        // could be static in doc type magic
        // @see https://regex101.com/r/tlvfTB/1
        return $this->hasStaticAnnotation($methodName, $classReflection);
    }
    private function hasStaticAnnotation(string $methodName, ClassReflection $classReflection) : bool
    {
        $resolvedPhpDocBlock = $classReflection->getResolvedPhpDoc();
        if (!$resolvedPhpDocBlock instanceof ResolvedPhpDocBlock) {
            return \false;
        }
        // @see https://regex101.com/r/7Zkej2/1
        return StringUtils::isMatch($resolvedPhpDocBlock->getPhpDocString(), '#@method\\s*static\\s*((([\\w\\|\\\\]+)|\\$this)*+(\\[\\])*)*\\s+\\b' . $methodName . '\\b#');
    }
}
