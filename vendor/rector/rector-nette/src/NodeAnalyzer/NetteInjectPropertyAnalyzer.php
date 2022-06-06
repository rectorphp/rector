<?php

declare (strict_types=1);
namespace Rector\Nette\NodeAnalyzer;

use PhpParser\Node\Stmt\Property;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Reflection\ReflectionResolver;
use Rector\Core\ValueObject\MethodName;
use Rector\FamilyTree\NodeAnalyzer\ClassChildAnalyzer;
final class NetteInjectPropertyAnalyzer
{
    /**
     * @readonly
     * @var \Rector\FamilyTree\NodeAnalyzer\ClassChildAnalyzer
     */
    private $classChildAnalyzer;
    /**
     * @readonly
     * @var \Rector\Core\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    public function __construct(\Rector\FamilyTree\NodeAnalyzer\ClassChildAnalyzer $classChildAnalyzer, \Rector\Core\Reflection\ReflectionResolver $reflectionResolver)
    {
        $this->classChildAnalyzer = $classChildAnalyzer;
        $this->reflectionResolver = $reflectionResolver;
    }
    public function canBeRefactored(\PhpParser\Node\Stmt\Property $property, \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo $phpDocInfo) : bool
    {
        if (!$phpDocInfo->hasByName('inject')) {
            throw new \Rector\Core\Exception\ShouldNotHappenException();
        }
        // it needs @var tag as well, to get the type - faster, put first :)
        if (!$this->isKnownPropertyType($phpDocInfo, $property)) {
            return \false;
        }
        $classReflection = $this->reflectionResolver->resolveClassReflection($property);
        if (!$classReflection instanceof \PHPStan\Reflection\ClassReflection) {
            return \false;
        }
        if ($classReflection->isAbstract()) {
            return \false;
        }
        if ($classReflection->isAnonymous()) {
            return \false;
        }
        if ($this->classChildAnalyzer->hasChildClassMethod($classReflection, \Rector\Core\ValueObject\MethodName::CONSTRUCT)) {
            return \false;
        }
        return $this->hasNoOrEmptyParamParentConstructor($classReflection);
    }
    public function hasNoOrEmptyParamParentConstructor(\PHPStan\Reflection\ClassReflection $classReflection) : bool
    {
        $parentClassMethods = $this->classChildAnalyzer->resolveParentClassMethods($classReflection, \Rector\Core\ValueObject\MethodName::CONSTRUCT);
        if ($parentClassMethods === []) {
            return \true;
        }
        // are there parent ctors? - has empty constructor params? it can be refactored
        foreach ($parentClassMethods as $parentClassMethod) {
            $parametersAcceptor = \PHPStan\Reflection\ParametersAcceptorSelector::selectSingle($parentClassMethod->getVariants());
            if ($parametersAcceptor->getParameters() !== []) {
                return \false;
            }
        }
        return \true;
    }
    private function isKnownPropertyType(\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo $phpDocInfo, \PhpParser\Node\Stmt\Property $property) : bool
    {
        if ($phpDocInfo->getVarTagValueNode() !== null) {
            return \true;
        }
        return $property->type !== null;
    }
}
