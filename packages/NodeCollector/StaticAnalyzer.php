<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\NodeCollector;

use RectorPrefix20220606\PHPStan\PhpDoc\ResolvedPhpDocBlock;
use RectorPrefix20220606\PHPStan\Reflection\ClassReflection;
use RectorPrefix20220606\PHPStan\Reflection\ReflectionProvider;
use RectorPrefix20220606\Rector\Core\Util\StringUtils;
final class StaticAnalyzer
{
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    public function __construct(ReflectionProvider $reflectionProvider)
    {
        $this->reflectionProvider = $reflectionProvider;
    }
    public function isStaticMethod(string $methodName, string $className) : bool
    {
        if (!$this->reflectionProvider->hasClass($className)) {
            return \false;
        }
        $classReflection = $this->reflectionProvider->getClass($className);
        if ($classReflection->hasNativeMethod($methodName)) {
            $methodReflection = $classReflection->getNativeMethod($methodName);
            if ($methodReflection->isStatic()) {
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
