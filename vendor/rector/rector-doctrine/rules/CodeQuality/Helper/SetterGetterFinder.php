<?php

declare (strict_types=1);
namespace Rector\Doctrine\CodeQuality\Helper;

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\TypeDeclaration\NodeAnalyzer\ClassMethodAndPropertyAnalyzer;
final class SetterGetterFinder
{
    /**
     * @readonly
     */
    private ClassMethodAndPropertyAnalyzer $classMethodAndPropertyAnalyzer;
    public function __construct(ClassMethodAndPropertyAnalyzer $classMethodAndPropertyAnalyzer)
    {
        $this->classMethodAndPropertyAnalyzer = $classMethodAndPropertyAnalyzer;
    }
    public function findGetterClassMethod(Class_ $class, string $propertyName): ?ClassMethod
    {
        foreach ($class->getMethods() as $classMethod) {
            if (!$this->classMethodAndPropertyAnalyzer->hasPropertyFetchReturn($classMethod, $propertyName)) {
                continue;
            }
            return $classMethod;
        }
        return null;
    }
    public function findSetterClassMethod(Class_ $class, string $propertyName): ?ClassMethod
    {
        foreach ($class->getMethods() as $classMethod) {
            if (!$this->classMethodAndPropertyAnalyzer->hasOnlyPropertyAssign($classMethod, $propertyName)) {
                continue;
            }
            return $classMethod;
        }
        return null;
    }
}
