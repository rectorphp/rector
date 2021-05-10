<?php

declare (strict_types=1);
namespace Rector\Generics\Filter;

use PHPStan\Analyser\Scope;
use PHPStan\PhpDocParser\Ast\PhpDoc\MethodTagValueNode;
use PHPStan\Reflection\ClassReflection;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\Generics\Reflection\ClassMethodAnalyzer;
use Rector\Generics\ValueObject\ChildParentClassReflections;
final class UnnededMethodTagValueNodeFilter
{
    /**
     * @var ClassMethodAnalyzer
     */
    private $classMethodAnalyzer;
    public function __construct(\Rector\Generics\Reflection\ClassMethodAnalyzer $classMethodAnalyzer)
    {
        $this->classMethodAnalyzer = $classMethodAnalyzer;
    }
    /**
     * @param MethodTagValueNode[] $methodTagValueNodes
     * @return MethodTagValueNode[]
     */
    public function filter(array $methodTagValueNodes, \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo $phpDocInfo, \Rector\Generics\ValueObject\ChildParentClassReflections $genericChildParentClassReflections, \PHPStan\Analyser\Scope $scope) : array
    {
        $methodTagValueNodes = $this->filterOutExistingMethodTagValuesNodes($methodTagValueNodes, $phpDocInfo);
        return $this->filterOutImplementedClassMethods($methodTagValueNodes, $genericChildParentClassReflections->getChildClassReflection(), $scope);
    }
    /**
     * @param MethodTagValueNode[] $methodTagValueNodes
     * @return MethodTagValueNode[]
     */
    private function filterOutExistingMethodTagValuesNodes(array $methodTagValueNodes, \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo $phpDocInfo) : array
    {
        $methodTagNames = $phpDocInfo->getMethodTagNames();
        if ($methodTagNames === []) {
            return $methodTagValueNodes;
        }
        $filteredMethodTagValueNodes = [];
        foreach ($methodTagValueNodes as $methodTagValueNode) {
            if (\in_array($methodTagValueNode->methodName, $methodTagNames, \true)) {
                continue;
            }
            $filteredMethodTagValueNodes[] = $methodTagValueNode;
        }
        return $filteredMethodTagValueNodes;
    }
    /**
     * @param MethodTagValueNode[] $methodTagValueNodes
     * @return MethodTagValueNode[]
     */
    private function filterOutImplementedClassMethods(array $methodTagValueNodes, \PHPStan\Reflection\ClassReflection $classReflection, \PHPStan\Analyser\Scope $scope) : array
    {
        $filteredMethodTagValueNodes = [];
        foreach ($methodTagValueNodes as $methodTagValueNode) {
            if ($this->classMethodAnalyzer->hasClassMethodDirectly($classReflection, $methodTagValueNode->methodName, $scope)) {
                continue;
            }
            $filteredMethodTagValueNodes[] = $methodTagValueNode;
        }
        return $filteredMethodTagValueNodes;
    }
}
