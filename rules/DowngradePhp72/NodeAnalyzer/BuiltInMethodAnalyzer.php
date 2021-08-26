<?php

declare (strict_types=1);
namespace Rector\DowngradePhp72\NodeAnalyzer;

use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Reflection\ClassReflection;
use Rector\FamilyTree\NodeAnalyzer\ClassChildAnalyzer;
use Rector\NodeNameResolver\NodeNameResolver;
final class BuiltInMethodAnalyzer
{
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @var \Rector\FamilyTree\NodeAnalyzer\ClassChildAnalyzer
     */
    private $classChildAnalyzer;
    public function __construct(\Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\FamilyTree\NodeAnalyzer\ClassChildAnalyzer $classChildAnalyzer)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->classChildAnalyzer = $classChildAnalyzer;
    }
    public function isImplementsBuiltInInterface(\PHPStan\Reflection\ClassReflection $classReflection, \PhpParser\Node\Stmt\ClassMethod $classMethod) : bool
    {
        if (!$classReflection->isClass()) {
            return \false;
        }
        $methodName = $this->nodeNameResolver->getName($classMethod);
        if ($this->classChildAnalyzer->hasChildClassMethod($classReflection, $methodName)) {
            return \false;
        }
        foreach ($classReflection->getInterfaces() as $interfaceReflection) {
            if (!$interfaceReflection->isBuiltIn()) {
                continue;
            }
            if (!$interfaceReflection->hasMethod($methodName)) {
                continue;
            }
            return \true;
        }
        return \false;
    }
}
