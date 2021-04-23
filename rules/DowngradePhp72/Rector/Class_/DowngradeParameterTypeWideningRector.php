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
use PHPStan\Type\Type;
use Rector\ChangesReporting\ValueObject\RectorWithLineChange;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\Application\File;
use Rector\DowngradePhp72\NodeAnalyzer\ClassLikeWithTraitsClassMethodResolver;
use Rector\DowngradePhp72\NodeAnalyzer\ParentChildClassMethodTypeResolver;
use Rector\DowngradePhp72\PhpDoc\NativeParamToPhpDocDecorator;
use Rector\NodeTypeResolver\Node\AttributeKey;
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
        ClassLikeWithTraitsClassMethodResolver $classLikeWithTraitsClassMethodResolver,
        ReflectionProvider $reflectionProvider,
        ParentChildClassMethodTypeResolver $parentChildClassMethodTypeResolver,
        NativeParamToPhpDocDecorator $nativeParamToPhpDocDecorator
    ) {
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
        $scope = $node->getAttribute(AttributeKey::SCOPE);
        if (! $scope instanceof Scope) {
            return null;
        }

        if ($this->isEmptyClassReflection($scope)) {
            return null;
        }

        $classMethods = $this->classLikeWithTraitsClassMethodResolver->resolve($node);
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

        // skip classes we cannot change
        foreach (array_keys($parameterTypesByParentClassLikes) as $className) {
            $classLike = $this->nodeRepository->findClassLike($className);
            if (! $classLike instanceof ClassLike) {
                return;
            }
        }

        // we need at least 2 types = 2 occurances of same method
        if (count($parameterTypesByParentClassLikes) <= 1) {
            return;
        }
        $this->refactorParameters($parameterTypesByParentClassLikes, $methodName, $position);
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
        $param->setAttribute(AttributeKey::ORIGINAL_NODE, null);

        // file from another file
        $file = $param->getAttribute(AttributeKey::FILE);
        if ($file instanceof File) {
            $rectorWithLineChange = new RectorWithLineChange($this, $param->getLine());
            $file->addRectorClassWithLine($rectorWithLineChange);
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

    /**
     * @param array<class-string, Type> $parameterTypesByParentClassLikes
     */
    private function refactorParameters(
        array $parameterTypesByParentClassLikes,
        string $methodName,
        int $paramPosition
    ): void {
        foreach (array_keys($parameterTypesByParentClassLikes) as $className) {
            $classLike = $this->nodeRepository->findClassLike($className);
            if (! $classLike instanceof ClassLike) {
                continue;
            }

            $classMethod = $classLike->getMethod($methodName);
            if (! $classMethod instanceof ClassMethod) {
                continue;
            }

            $this->removeParamTypeFromMethod($classLike, $paramPosition, $classMethod);
        }
    }
}
