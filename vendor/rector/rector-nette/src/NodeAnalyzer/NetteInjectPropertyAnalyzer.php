<?php

declare (strict_types=1);
namespace Rector\Nette\NodeAnalyzer;

use PhpParser\Node\Stmt\Property;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\ValueObject\MethodName;
use Rector\FamilyTree\NodeAnalyzer\ClassChildAnalyzer;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class NetteInjectPropertyAnalyzer
{
    /**
     * @readonly
     * @var \Rector\FamilyTree\NodeAnalyzer\ClassChildAnalyzer
     */
    private $classChildAnalyzer;
    public function __construct(\Rector\FamilyTree\NodeAnalyzer\ClassChildAnalyzer $classChildAnalyzer)
    {
        $this->classChildAnalyzer = $classChildAnalyzer;
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
        $scope = $property->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        if (!$scope instanceof \PHPStan\Analyser\Scope) {
            return \false;
        }
        $classReflection = $scope->getClassReflection();
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
