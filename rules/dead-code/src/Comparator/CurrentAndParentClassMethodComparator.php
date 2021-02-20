<?php

declare(strict_types=1);

namespace Rector\DeadCode\Comparator;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\PhpParser\Comparing\NodeComparator;
use Rector\DeadCode\Comparator\Parameter\ParameterDefaultsComparator;
use Rector\DeadCode\Comparator\Parameter\ParameterTypeComparator;
use Rector\NodeCollector\NodeCollector\NodeRepository;
use Rector\NodeCollector\Reflection\MethodReflectionProvider;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class CurrentAndParentClassMethodComparator
{
    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var NodeComparator
     */
    private $nodeComparator;

    /**
     * @var NodeRepository
     */
    private $nodeRepository;

    /**
     * @var MethodReflectionProvider
     */
    private $methodReflectionProvider;

    /**
     * @var ParameterDefaultsComparator
     */
    private $parameterDefaultsComparator;

    /**
     * @var ParameterTypeComparator
     */
    private $parameterTypeComparator;

    public function __construct(
        NodeNameResolver $nodeNameResolver,
        NodeRepository $nodeRepository,
        MethodReflectionProvider $methodReflectionProvider,
        ParameterDefaultsComparator $parameterDefaultsComparator,
        ParameterTypeComparator $parameterTypeComparator,
        NodeComparator $nodeComparator
    ) {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->nodeRepository = $nodeRepository;
        $this->methodReflectionProvider = $methodReflectionProvider;
        $this->parameterDefaultsComparator = $parameterDefaultsComparator;
        $this->parameterTypeComparator = $parameterTypeComparator;
        $this->nodeComparator = $nodeComparator;
    }

    public function isParentCallMatching(ClassMethod $classMethod, StaticCall $staticCall): bool
    {
        if (! $this->isSameMethodParentCall($classMethod, $staticCall)) {
            return false;
        }

        if (! $this->areArgsAndParamsEqual($staticCall->args, $classMethod->params)) {
            return false;
        }

        if (! $this->parameterTypeComparator->compareCurrentClassMethodAndParentStaticCall($classMethod, $staticCall)) {
            return false;
        }

        return ! $this->isParentClassMethodVisibilityOrDefaultOverride($classMethod, $staticCall);
    }

    private function isSameMethodParentCall(ClassMethod $classMethod, StaticCall $staticCall): bool
    {
        if (! $this->nodeNameResolver->areNamesEqual($staticCall->name, $classMethod->name)) {
            return false;
        }

        return $this->nodeNameResolver->isName($staticCall->class, 'parent');
    }

    /**
     * @param Arg[] $parentStaticCallArgs
     * @param Param[] $currentClassMethodParams
     */
    private function areArgsAndParamsEqual(array $parentStaticCallArgs, array $currentClassMethodParams): bool
    {
        if (count($parentStaticCallArgs) !== count($currentClassMethodParams)) {
            return false;
        }

        if ($parentStaticCallArgs === []) {
            return true;
        }

        foreach ($parentStaticCallArgs as $key => $arg) {
            if (! isset($currentClassMethodParams[$key])) {
                return false;
            }

            // this only compares variable name, but those can be differnt, so its kinda useless
            $param = $currentClassMethodParams[$key];
            if (! $this->nodeComparator->areNodesEqual($param->var, $arg->value)) {
                return false;
            }
        }

        return true;
    }

    private function isParentClassMethodVisibilityOrDefaultOverride(
        ClassMethod $classMethod,
        StaticCall $staticCall
    ): bool {
        /** @var string $className */
        $className = $staticCall->getAttribute(AttributeKey::CLASS_NAME);

        $parentClassName = get_parent_class($className);
        if (! $parentClassName) {
            throw new ShouldNotHappenException();
        }

        /** @var string $methodName */
        $methodName = $this->nodeNameResolver->getName($staticCall->name);

        $parentClassMethod = $this->nodeRepository->findClassMethod($parentClassName, $methodName);
        if ($parentClassMethod === null) {
            return $this->checkOverrideUsingReflection($classMethod, $parentClassName, $methodName);
        }
        if (! $parentClassMethod->isProtected()) {
            return $this->checkOverrideUsingReflection($classMethod, $parentClassName, $methodName);
        }
        if (! $classMethod->isPublic()) {
            return $this->checkOverrideUsingReflection($classMethod, $parentClassName, $methodName);
        }
        return true;
    }

    private function checkOverrideUsingReflection(
        ClassMethod $classMethod,
        string $parentClassName,
        string $methodName
    ): bool {
        // @todo use phpstan reflecoitn
        $scope = $classMethod->getAttribute(AttributeKey::SCOPE);
        if (! $scope instanceof Scope) {
            throw new ShouldNotHappenException();
        }

        $parentMethodReflection = $this->methodReflectionProvider->provideByClassAndMethodName(
            $parentClassName,
            $methodName,
            $scope
        );

        // 3rd party code
        if ($parentMethodReflection !== null) {
            if (! $parentMethodReflection->isPrivate() && ! $parentMethodReflection->isPublic() && $classMethod->isPublic()) {
                return true;
            }

            if ($parentMethodReflection->isInternal()->yes()) {
                // we can't know for certain so we assume its a override with purpose
                return true;
            }

            if ($this->areParameterDefaultsDifferent($classMethod, $parentMethodReflection)) {
                return true;
            }
        }

        return false;
    }

    private function areParameterDefaultsDifferent(
        ClassMethod $classMethod,
        MethodReflection $methodReflection
    ): bool {
        $parameterReflections = $this->methodReflectionProvider->getParameterReflectionsFromMethodReflection(
            $methodReflection
        );

        foreach ($parameterReflections as $key => $parameterReflection) {
            if (! isset($classMethod->params[$key])) {
                if ($parameterReflection->getDefaultValue() !== null) {
                    continue;
                }

                return true;
            }

            $methodParam = $classMethod->params[$key];

            if ($this->parameterDefaultsComparator->areDefaultValuesDifferent(
                $parameterReflection,
                $methodParam
            )) {
                return true;
            }
        }

        return false;
    }
}
