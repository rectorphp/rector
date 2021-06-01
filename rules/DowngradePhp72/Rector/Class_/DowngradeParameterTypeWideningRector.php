<?php

declare(strict_types=1);

namespace Rector\DowngradePhp72\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Interface_;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Rector\AbstractRector;
use Rector\DowngradePhp72\NodeAnalyzer\ClassLikeWithTraitsClassMethodResolver;
use Rector\DowngradePhp72\NodeAnalyzer\ParamContravariantDetector;
use Rector\DowngradePhp72\NodeAnalyzer\ParentChildClassMethodTypeResolver;
use Rector\DowngradePhp72\PhpDoc\NativeParamToPhpDocDecorator;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\PHPStan\Type\TypeFactory;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use PhpParser\Node\Name\FullyQualified;

/**
 * @changelog https://www.php.net/manual/en/migration72.new-features.php#migration72.new-features.param-type-widening
 * @see https://3v4l.org/fOgSE
 *
 * @see \Rector\Tests\DowngradePhp72\Rector\Class_\DowngradeParameterTypeWideningRector\DowngradeParameterTypeWideningRectorTest
 */
final class DowngradeParameterTypeWideningRector extends AbstractRector
{
    public function __construct(
        private ClassLikeWithTraitsClassMethodResolver $classLikeWithTraitsClassMethodResolver,
        private ParentChildClassMethodTypeResolver $parentChildClassMethodTypeResolver,
        private NativeParamToPhpDocDecorator $nativeParamToPhpDocDecorator,
        private ParamContravariantDetector $paramContravariantDetector,
        private TypeFactory $typeFactory
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change param type to match the lowest type in whole family tree', [
            new CodeSample(
                <<<'CODE_SAMPLE'
interface SomeInterface
{
    public function test(array $input);
}

final class SomeClass implements SomeInterface
{
    public function test($input)
    {
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
interface SomeInterface
{
    /**
     * @param mixed[] $input
     */
    public function test($input);
}

final class SomeClass implements SomeInterface
{
    public function test($input)
    {
    }
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
        return [Class_::class, Interface_::class];
    }

    /**
     * @param Class_|Interface_ $node
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

        if ($this->hasExtendExternal($node)) {
            return null;
        }

        $hasChanged = false;
        $classMethods = $this->classLikeWithTraitsClassMethodResolver->resolve($node);

        foreach ($classMethods as $classMethod) {
            if ($this->skipClassMethod($classMethod, $scope)) {
                continue;
            }

            // refactor here
            if ($this->refactorClassMethod($classMethod, $scope) !== null) {
                $hasChanged = true;
            }
        }

        if ($hasChanged) {
            return $node;
        }

        return null;
    }

    /**
     * @param Class_|Interface_ $node
     */
    private function hasExtendExternal(Node $node): bool
    {
        if ($node->extends instanceof FullyQualified) {
            $className   = (string) $this->getName($node->extends);
            $parentFound = (bool) $this->nodeRepository->findClass($className);
            if (! $parentFound) {
                return true;
            }
        }

        return false;
    }

    /**
     * The topmost class is the source of truth, so we go only down to avoid up/down collission
     */
    private function refactorClassMethod(ClassMethod $classMethod, Scope $scope): ?ClassMethod
    {
        $classReflection = $scope->getClassReflection();
        if (! $classReflection instanceof ClassReflection) {
            return null;
        }

        /** @var string $methodName */
        $methodName = $this->nodeNameResolver->getName($classMethod);

        $hasChanged = false;
        foreach ($classMethod->params as $position => $param) {
            if (! is_int($position)) {
                throw new ShouldNotHappenException();
            }

            // Resolve the types in:
            // - all ancestors + their descendant classes
            // @todo - all implemented interfaces + their implementing classes
            $parameterTypesByParentClassLikes = $this->parentChildClassMethodTypeResolver->resolve(
                $classReflection,
                $methodName,
                $position,
                $scope
            );

            $uniqueTypes = $this->typeFactory->uniquateTypes($parameterTypesByParentClassLikes);
            if (count($uniqueTypes) <= 1) {
                continue;
            }

            $this->removeParamTypeFromMethod($classMethod, $param);
            $hasChanged = true;
        }

        if ($hasChanged) {
            return $classMethod;
        }

        return null;
    }

    private function removeParamTypeFromMethod(ClassMethod $classMethod, Param $param): void
    {
        // It already has no type => nothing to do - check original param, as it could have been removed by this rule
        $originalParam = $param->getAttribute(AttributeKey::ORIGINAL_NODE);
        if ($originalParam instanceof Param) {
            if ($originalParam->type === null) {
                return;
            }
        } elseif ($param->type === null) {
            return;
        }

        // Add the current type in the PHPDoc
        $this->nativeParamToPhpDocDecorator->decorate($classMethod, $param);
        $param->type = null;
    }

    private function skipClassMethod(ClassMethod $classMethod, Scope $classScope): bool
    {
        if ($classMethod->isMagic()) {
            return true;
        }

        if ($classMethod->params === []) {
            return true;
        }

        if ($this->paramContravariantDetector->hasChildMethod($classMethod, $classScope)) {
            return false;
        }

        return ! $this->paramContravariantDetector->hasParentMethod($classMethod, $classScope);
    }

    private function isEmptyClassReflection(Scope $scope): bool
    {
        $classReflection = $scope->getClassReflection();
        if (! $classReflection instanceof ClassReflection) {
            return true;
        }

        if ($classReflection->isInterface()) {
            return false;
        }

        return count($classReflection->getAncestors()) === 1;
    }
}
