<?php

declare(strict_types=1);

namespace Rector\DowngradePhp72\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Interface_;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use ReflectionMethod;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see https://www.php.net/manual/en/migration72.new-features.php#migration72.new-features.param-type-widening
 *
 * @see \Rector\DowngradePhp72\Tests\Rector\ClassMethod\DowngradeParameterTypeWideningRector\DowngradeParameterTypeWideningRectorTest
 */
final class DowngradeParameterTypeWideningRector extends AbstractRector
{
    /**
     * @var PhpDocTypeChanger
     */
    private $phpDocTypeChanger;

    public function __construct(PhpDocTypeChanger $phpDocTypeChanger)
    {
        $this->phpDocTypeChanger = $phpDocTypeChanger;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Remove argument type declarations in the parent and in all child classes, whenever some child class removes it',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
interface A
{
    public function test(array $input);
}

class B implements A
{
    public function test($input){} // type omitted for $input
}

class C implements A
{
    public function test(array $input){}
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
interface A
{
    /**
     * @param array $input
     */
    public function test($input);
}

class B implements A
{
    public function test($input){} // type omitted for $input
}

class C implements A
{
    /**
     * @param array $input
     */
    public function test($input);
}
CODE_SAMPLE
                ),

            ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Function_::class, ClassMethod::class];
    }

    /**
     * @param ClassMethod|Function_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node->params === null) {
            return null;
        }
        if ($node->params === []) {
            return null;
        }
        foreach ($node->params as $position => $param) {
            $this->refactorParamForAncestorsAndSiblings($param, $node, (int) $position);
        }

        return null;
    }

    private function refactorParamForAncestorsAndSiblings(Param $param, FunctionLike $functionLike, int $position): void
    {
        // The param on the child class must have no type
        if ($param->type !== null) {
            return;
        }

        $scope = $functionLike->getAttribute(AttributeKey::SCOPE);
        if (! $scope instanceof Scope) {
            // possibly trait
            return;
        }

        $classReflection = $scope->getClassReflection();
        if (! $classReflection instanceof ClassReflection) {
            return;
        }

        /** @var string $methodName */
        $methodName = $this->getName($functionLike);
        $paramName = $this->getName($param);

        // Obtain the list of the ancestors classes and implemented interfaces
        // with a different signature

        /** @var ClassLike[] $ancestorAndInterfaces */
        $ancestorAndInterfaces = array_merge(
            $this->getClassesWithDifferentSignature($classReflection, $methodName, $paramName),
            $this->getInterfacesWithDifferentSignature($classReflection, $methodName, $paramName)
        );

        // Remove the types in:
        // - all ancestors + their descendant classes
        // - all implemented interfaces + their implementing classes
        foreach ($ancestorAndInterfaces as $ancestorClassOrInterface) {
            /** @var string $parentClassName */
            $parentClassName = $ancestorClassOrInterface->getAttribute(AttributeKey::CLASS_NAME);
            $classMethod = $this->nodeRepository->findClassMethod($parentClassName, $methodName);
            /**
             * If it doesn't find the method, it's because the method
             * lives somewhere else.
             * For instance, in test "interface_on_parent_class.php.inc",
             * the ancestor abstract class is also retrieved
             * as containing the method, but it does not: it is
             * in its implemented interface. That happens because
             * `ReflectionMethod` doesn't allow to do do the distinction.
             * The interface is also retrieve though, so that method
             * will eventually be refactored.
             */
            if (! $classMethod instanceof ClassMethod) {
                continue;
            }
            $this->removeParamTypeFromMethod($ancestorClassOrInterface, $position, $classMethod);
            $this->removeParamTypeFromMethodForChildren($parentClassName, $methodName, $position);
        }
    }

    /**
     * Obtain the list of the ancestors classes with a different signature
     * @return Class_[]
     */
    private function getClassesWithDifferentSignature(
        ClassReflection $classReflection,
        string $methodName,
        string $paramName
    ): array {
        // 1. All ancestor classes with different signature
        $ancestorClassNames = array_filter(
            $classReflection->getParentClassesNames(),
            function (string $ancestorClassName) use ($methodName, $paramName): bool {
                return $this->hasMethodWithTypedParam($ancestorClassName, $methodName, $paramName);
            }
        );

        $classes = [];
        foreach ($ancestorClassNames as $ancestorClassName) {
            $class = $this->nodeRepository->findClass($ancestorClassName);
            if (! $class instanceof Class_) {
                continue;
            }

            $classes[] = $class;
        }

        return $classes;
    }

    /**
     * Obtain the list of the implemented interfaces with a different signature
     * @return Interface_[]
     */
    private function getInterfacesWithDifferentSignature(
        ClassReflection $classReflection,
        string $methodName,
        string $paramName
    ): array {
        $interfaceClassNames = array_map(
            function (ClassReflection $interfaceReflection): string {
                return $interfaceReflection->getName();
            },
            $classReflection->getInterfaces()
        );
        $refactorableInterfaceClassNames = array_filter(
            $interfaceClassNames,
            function (string $interfaceClassName) use ($methodName, $paramName): bool {
                return $this->hasMethodWithTypedParam($interfaceClassName, $methodName, $paramName);
            }
        );

        $interfaces = [];
        foreach ($refactorableInterfaceClassNames as $refactorableInterfaceClassName) {
            $interface = $this->nodeRepository->findInterface($refactorableInterfaceClassName);
            if (! $interface instanceof Interface_) {
                continue;
            }

            $interfaces[] = $interface;
        }

        return $interfaces;
    }

    private function removeParamTypeFromMethod(
        ClassLike $classLike,
        int $position,
        ClassMethod $classMethod
    ): void {
        $classMethodName = $this->getName($classMethod);
        $currentClassMethod = $classLike->getMethod($classMethodName);
        if (! $currentClassMethod instanceof ClassMethod) {
            return;
        }

        if (! isset($currentClassMethod->params[$position])) {
            return;
        }

        $param = $currentClassMethod->params[$position];

        // It already has no type => nothing to do
        if ($param->type === null) {
            return;
        }

        // Add the current type in the PHPDoc
        $this->addPHPDocParamTypeToMethod($classMethod, $param);

        // Remove the type
        $param->type = null;
    }

    private function removeParamTypeFromMethodForChildren(
        string $parentClassName,
        string $methodName,
        int $position
    ): void {
        $childrenClassLikes = $this->nodeRepository->findClassesAndInterfacesByType($parentClassName);
        foreach ($childrenClassLikes as $childClassLike) {
            $childClassName = $childClassLike->getAttribute(AttributeKey::CLASS_NAME);
            if ($childClassName === null) {
                continue;
            }
            $childClassMethod = $this->nodeRepository->findClassMethod($childClassName, $methodName);
            if (! $childClassMethod instanceof ClassMethod) {
                continue;
            }
            $this->removeParamTypeFromMethod($childClassLike, $position, $childClassMethod);
        }
    }

    private function hasMethodWithTypedParam(string $parentClassName, string $methodName, string $paramName): bool
    {
        if (! method_exists($parentClassName, $methodName)) {
            return false;
        }

        $parentReflectionMethod = new ReflectionMethod($parentClassName, $methodName);

        $parentReflectionMethodParams = $parentReflectionMethod->getParameters();
        foreach ($parentReflectionMethodParams as $reflectionParameter) {
            if ($reflectionParameter->name === $paramName && $reflectionParameter->getType() !== null) {
                return true;
            }
        }

        return false;
    }

    /**
     * Add the current param type in the PHPDoc
     */
    private function addPHPDocParamTypeToMethod(ClassMethod $classMethod, Param $param): void
    {
        if ($param->type === null) {
            return;
        }

        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);

        $paramName = $this->getName($param);
        $mappedCurrentParamType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($param->type);
        $this->phpDocTypeChanger->changeParamType($phpDocInfo, $mappedCurrentParamType, $param, $paramName);
    }
}
