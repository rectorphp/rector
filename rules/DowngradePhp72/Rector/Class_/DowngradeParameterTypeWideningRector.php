<?php

declare(strict_types=1);

namespace Rector\DowngradePhp72\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ReflectionProvider;
use Rector\ChangesReporting\ValueObject\RectorWithLineChange;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\Application\File;
use Rector\DowngradePhp72\NodeAnalyzer\ClassLikeWithTraitsClassMethodResolver;
use Rector\DowngradePhp72\NodeAnalyzer\ParentChildClassMethodTypeResolver;
use Rector\DowngradePhp72\PhpDoc\NativeParamToPhpDocDecorator;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\PHPStan\Type\TypeFactory;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @changelog https://www.php.net/manual/en/migration72.new-features.php#migration72.new-features.param-type-widening
 * @see https://3v4l.org/fOgSE
 *
 * @see \Rector\Tests\DowngradePhp72\Rector\Class_\DowngradeParameterTypeWideningRector\DowngradeParameterTypeWideningRectorTest
 */
final class DowngradeParameterTypeWideningRector extends AbstractRector
{
    /**
     * @var TypeFactory
     */
    private $typeFactory;

    /**
     * @var ClassLikeWithTraitsClassMethodResolver
     */
    private $classLikeWithTraitsClassMethodResolver;

    /**
     * @var ReflectionProvider
     */
    private $reflectionProvider;

    /**
     * @var ParentChildClassMethodTypeResolver
     */
    private $parentChildClassMethodTypeResolver;

    /**
     * @var NativeParamToPhpDocDecorator
     */
    private $nativeParamToPhpDocDecorator;

    public function __construct(
        TypeFactory $typeFactory,
        ClassLikeWithTraitsClassMethodResolver $classLikeWithTraitsClassMethodResolver,
        ReflectionProvider $reflectionProvider,
        ParentChildClassMethodTypeResolver $parentChildClassMethodTypeResolver,
        NativeParamToPhpDocDecorator $nativeParamToPhpDocDecorator
    ) {
        $this->typeFactory = $typeFactory;
        $this->classLikeWithTraitsClassMethodResolver = $classLikeWithTraitsClassMethodResolver;
        $this->reflectionProvider = $reflectionProvider;
        $this->parentChildClassMethodTypeResolver = $parentChildClassMethodTypeResolver;
        $this->nativeParamToPhpDocDecorator = $nativeParamToPhpDocDecorator;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Change param type to match the lowest type in whole family tree',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
interface A
{
    public function test(array $input);
}

class C implements A
{
    public function test($input){}
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
interface A
{
    public function test(array $input);
}

class C implements A
{
    public function test(array $input){}
}
CODE_SAMPLE
                ),
            ]);
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }

    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $classMethods = $this->classLikeWithTraitsClassMethodResolver->resolve($node);

        $scope = $node->getAttribute(AttributeKey::SCOPE);
        if (! $scope instanceof Scope) {
            return null;
        }

        if ($this->isEmptyClassReflection($scope)) {
            return null;
        }

        foreach ($classMethods as $classMethod) {
            $this->refactorClassMethod($classMethod, $scope);
        }

        return $node;
    }

    /**
     * The topmost class is the source of truth, so we go only down to avoid up/down collission
     */
    private function refactorParamForSelfAndSiblings(ClassMethod $classMethod, int $position, Scope $scope): void
    {
        $class = $classMethod->getAttribute(AttributeKey::CLASS_NODE);
        if ($class === null) {
            return;
        }

        $className = $this->getName($class);
        if ($className === null) {
            return;
        }

        if (! $this->reflectionProvider->hasClass($className)) {
            return;
        }

        $classReflection = $this->reflectionProvider->getClass($className);

        /** @var string $methodName */
        $methodName = $this->nodeNameResolver->getName($classMethod);

        // Remove the types in:
        // - all ancestors + their descendant classes
        // - all implemented interfaces + their implementing classes
        $parameterTypesByParentClassLikes = $this->parentChildClassMethodTypeResolver->resolve(
            $classReflection,
            $methodName,
            $position,
            $scope
        );

        $uniqueParameterTypes = $this->typeFactory->uniquateTypes($parameterTypesByParentClassLikes);

        // we need at least 2 unique types
        if (count($uniqueParameterTypes) === 1) {
            return;
        }

        $this->refactorClassWithAncestorsAndChildren($classReflection, $methodName, $position);
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
        $this->nativeParamToPhpDocDecorator->decorate($classMethod, $param);

        // Remove the type
        $param->type = null;

        // file from another file
        $file = $param->getAttribute(AttributeKey::FILE);
        if ($file instanceof File) {
            $rectorWithLineChange = new RectorWithLineChange($this, $param->getLine());
            $file->addRectorClassWithLine($rectorWithLineChange);
        }
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

//    /**
//     * Add the current param type in the PHPDoc
//     */
//    private function addPHPDocParamTypeToMethod(ClassMethod $classMethod, Param $param): void
//    {
//        if ($param->type === null) {
//            return;
//        }
//
//        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);
//
//        $paramName = $this->getName($param);
//        $mappedCurrentParamType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($param->type);
//        $this->phpDocTypeChanger->changeParamType($phpDocInfo, $mappedCurrentParamType, $param, $paramName);
//    }

    private function refactorClassWithAncestorsAndChildren(
        ClassReflection $classReflection,
        string $methodName,
        int $position
    ): void {
        foreach ($classReflection->getAncestors() as $ancestorClassRelection) {
            $classLike = $this->nodeRepository->findClassLike($ancestorClassRelection->getName());
            if (! $classLike instanceof ClassLike) {
                continue;
            }

            $currentClassMethod = $classLike->getMethod($methodName);
            if (! $currentClassMethod instanceof ClassMethod) {
                continue;
            }

            $className = $this->getName($classLike);
            if ($className === null) {
                continue;
            }

            /**
             * If it doesn't find the method, it's because the method
             * lives somewhere else.
             * For instance, in test "interface_on_parent_class.php.inc",
             * the ancestorClassReflection abstract class is also retrieved
             * as containing the method, but it does not: it is
             * in its implemented interface. That happens because
             * `ReflectionMethod` doesn't allow to do do the distinction.
             * The interface is also retrieve though, so that method
             * will eventually be refactored.
             */

            $this->removeParamTypeFromMethod($classLike, $position, $currentClassMethod);
//            $this->removeParamTypeFromMethodForChildren($className, $methodName, $position);
        }
    }

    private function refactorClassMethod(ClassMethod $classMethod, Scope $classScope): void
    {
        if ($classMethod->isMagic()) {
            return;
        }

        if ($classMethod->params === []) {
            return;
        }

        foreach (array_keys($classMethod->params) as $position) {
            $this->refactorParamForSelfAndSiblings($classMethod, (int) $position, $classScope);
        }
    }

    private function isEmptyClassReflection(Scope $scope): bool
    {
        $classReflection = $scope->getClassReflection();
        if (! $classReflection instanceof ClassReflection) {
            return true;
        }

        return count($classReflection->getAncestors()) === 1;
    }
}
