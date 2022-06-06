<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Nette\NodeAnalyzer;

use RectorPrefix20220606\PhpParser\Node\Stmt\Property;
use RectorPrefix20220606\PHPStan\Reflection\ClassReflection;
use RectorPrefix20220606\PHPStan\Reflection\ParametersAcceptorSelector;
use RectorPrefix20220606\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use RectorPrefix20220606\Rector\Core\Exception\ShouldNotHappenException;
use RectorPrefix20220606\Rector\Core\Reflection\ReflectionResolver;
use RectorPrefix20220606\Rector\Core\ValueObject\MethodName;
use RectorPrefix20220606\Rector\FamilyTree\NodeAnalyzer\ClassChildAnalyzer;
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
    public function __construct(ClassChildAnalyzer $classChildAnalyzer, ReflectionResolver $reflectionResolver)
    {
        $this->classChildAnalyzer = $classChildAnalyzer;
        $this->reflectionResolver = $reflectionResolver;
    }
    public function canBeRefactored(Property $property, PhpDocInfo $phpDocInfo) : bool
    {
        if (!$phpDocInfo->hasByName('inject')) {
            throw new ShouldNotHappenException();
        }
        // it needs @var tag as well, to get the type - faster, put first :)
        if (!$this->isKnownPropertyType($phpDocInfo, $property)) {
            return \false;
        }
        $classReflection = $this->reflectionResolver->resolveClassReflection($property);
        if (!$classReflection instanceof ClassReflection) {
            return \false;
        }
        if ($classReflection->isAbstract()) {
            return \false;
        }
        if ($classReflection->isAnonymous()) {
            return \false;
        }
        if ($this->classChildAnalyzer->hasChildClassMethod($classReflection, MethodName::CONSTRUCT)) {
            return \false;
        }
        return $this->hasNoOrEmptyParamParentConstructor($classReflection);
    }
    public function hasNoOrEmptyParamParentConstructor(ClassReflection $classReflection) : bool
    {
        $parentClassMethods = $this->classChildAnalyzer->resolveParentClassMethods($classReflection, MethodName::CONSTRUCT);
        if ($parentClassMethods === []) {
            return \true;
        }
        // are there parent ctors? - has empty constructor params? it can be refactored
        foreach ($parentClassMethods as $parentClassMethod) {
            $parametersAcceptor = ParametersAcceptorSelector::selectSingle($parentClassMethod->getVariants());
            if ($parametersAcceptor->getParameters() !== []) {
                return \false;
            }
        }
        return \true;
    }
    private function isKnownPropertyType(PhpDocInfo $phpDocInfo, Property $property) : bool
    {
        if ($phpDocInfo->getVarTagValueNode() !== null) {
            return \true;
        }
        return $property->type !== null;
    }
}
