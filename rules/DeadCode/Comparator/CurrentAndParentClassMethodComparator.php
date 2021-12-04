<?php

declare (strict_types=1);
namespace Rector\DeadCode\Comparator;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\VariadicPlaceholder;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use Rector\Core\Enum\ObjectReference;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\PhpParser\Comparing\NodeComparator;
use Rector\DeadCode\Comparator\Parameter\ParameterDefaultsComparator;
use Rector\DeadCode\Comparator\Parameter\ParameterTypeComparator;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class CurrentAndParentClassMethodComparator
{
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\DeadCode\Comparator\Parameter\ParameterDefaultsComparator
     */
    private $parameterDefaultsComparator;
    /**
     * @readonly
     * @var \Rector\DeadCode\Comparator\Parameter\ParameterTypeComparator
     */
    private $parameterTypeComparator;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Comparing\NodeComparator
     */
    private $nodeComparator;
    public function __construct(\Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\DeadCode\Comparator\Parameter\ParameterDefaultsComparator $parameterDefaultsComparator, \Rector\DeadCode\Comparator\Parameter\ParameterTypeComparator $parameterTypeComparator, \Rector\Core\PhpParser\Comparing\NodeComparator $nodeComparator)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->parameterDefaultsComparator = $parameterDefaultsComparator;
        $this->parameterTypeComparator = $parameterTypeComparator;
        $this->nodeComparator = $nodeComparator;
    }
    public function isParentCallMatching(\PhpParser\Node\Stmt\ClassMethod $classMethod, \PhpParser\Node\Expr\StaticCall $staticCall) : bool
    {
        if (!$this->isSameMethodParentCall($classMethod, $staticCall)) {
            return \false;
        }
        if (!$this->areArgsAndParamsEqual($staticCall->args, $classMethod->params)) {
            return \false;
        }
        if (!$this->parameterTypeComparator->isClassMethodIdenticalToParentStaticCall($classMethod, $staticCall)) {
            return \false;
        }
        return !$this->isParentClassMethodVisibilityOrDefaultOverride($classMethod, $staticCall);
    }
    private function isSameMethodParentCall(\PhpParser\Node\Stmt\ClassMethod $classMethod, \PhpParser\Node\Expr\StaticCall $staticCall) : bool
    {
        if (!$this->nodeNameResolver->areNamesEqual($staticCall->name, $classMethod->name)) {
            return \false;
        }
        return $this->nodeNameResolver->isName($staticCall->class, \Rector\Core\Enum\ObjectReference::PARENT()->getValue());
    }
    /**
     * @param Arg[]|VariadicPlaceholder[] $parentStaticCallArgs
     * @param Param[] $currentClassMethodParams
     */
    private function areArgsAndParamsEqual(array $parentStaticCallArgs, array $currentClassMethodParams) : bool
    {
        if (\count($parentStaticCallArgs) !== \count($currentClassMethodParams)) {
            return \false;
        }
        if ($parentStaticCallArgs === []) {
            return \true;
        }
        foreach ($parentStaticCallArgs as $key => $arg) {
            if (!isset($currentClassMethodParams[$key])) {
                return \false;
            }
            if (!$arg instanceof \PhpParser\Node\Arg) {
                continue;
            }
            // this only compares variable name, but those can be differnt, so its kinda useless
            $param = $currentClassMethodParams[$key];
            if (!$this->nodeComparator->areNodesEqual($param->var, $arg->value)) {
                return \false;
            }
        }
        return \true;
    }
    private function isParentClassMethodVisibilityOrDefaultOverride(\PhpParser\Node\Stmt\ClassMethod $classMethod, \PhpParser\Node\Expr\StaticCall $staticCall) : bool
    {
        $scope = $classMethod->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        if (!$scope instanceof \PHPStan\Analyser\Scope) {
            return \false;
        }
        $classReflection = $scope->getClassReflection();
        if (!$classReflection instanceof \PHPStan\Reflection\ClassReflection) {
            return \false;
        }
        $methodName = $this->nodeNameResolver->getName($staticCall->name);
        if ($methodName === null) {
            return \false;
        }
        foreach ($classReflection->getParents() as $parentClassReflection) {
            if (!$parentClassReflection->hasMethod($methodName)) {
                continue;
            }
            $nativeParentClassReflection = $parentClassReflection->getNativeReflection();
            $nativeParentClassMethodReflection = $nativeParentClassReflection->getMethod($methodName);
            if (!$nativeParentClassMethodReflection->isProtected()) {
                return $this->isOverridingParentParameters($classMethod, $parentClassReflection, $methodName);
            }
            if (!$nativeParentClassMethodReflection->isPublic()) {
                return $this->isOverridingParentParameters($classMethod, $parentClassReflection, $methodName);
            }
            return \true;
        }
        return \false;
    }
    private function isOverridingParentParameters(\PhpParser\Node\Stmt\ClassMethod $classMethod, \PHPStan\Reflection\ClassReflection $classReflection, string $methodName) : bool
    {
        $scope = $classMethod->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        if (!$scope instanceof \PHPStan\Analyser\Scope) {
            throw new \Rector\Core\Exception\ShouldNotHappenException();
        }
        $parentMethodReflection = $classReflection->getMethod($methodName, $scope);
        // 3rd party code
        if (!$parentMethodReflection->isPrivate() && !$parentMethodReflection->isPublic() && $classMethod->isPublic()) {
            return \true;
        }
        if ($parentMethodReflection->isInternal()->yes()) {
            // we can't know for certain so we assume its a override with purpose
            return \true;
        }
        return $this->areParameterDefaultsDifferent($classMethod, $parentMethodReflection);
    }
    private function areParameterDefaultsDifferent(\PhpParser\Node\Stmt\ClassMethod $classMethod, \PHPStan\Reflection\MethodReflection $methodReflection) : bool
    {
        $parametersAcceptor = \PHPStan\Reflection\ParametersAcceptorSelector::selectSingle($methodReflection->getVariants());
        foreach ($parametersAcceptor->getParameters() as $key => $parameterReflection) {
            if (!isset($classMethod->params[$key])) {
                if ($parameterReflection->getDefaultValue() !== null) {
                    continue;
                }
                return \true;
            }
            $methodParam = $classMethod->params[$key];
            if ($this->parameterDefaultsComparator->areDefaultValuesDifferent($parameterReflection, $methodParam)) {
                return \true;
            }
        }
        return \false;
    }
}
