<?php

declare(strict_types=1);

namespace Rector\DowngradePhp72\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use Rector\Core\Rector\AbstractRector;
use Rector\DowngradePhp72\NodeAnalyzer\ParentChildClassMethodTypeResolver;
use Rector\DowngradePhp72\PhpDoc\NativeParamToPhpDocDecorator;
use Rector\DowngradePhp72\PHPStan\ClassLikeScopeResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\PHPStan\Type\TypeFactory;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @changelog https://www.php.net/manual/en/migration72.new-features.php#migration72.new-features.param-type-widening
 * @see https://3v4l.org/fOgSE
 *
 * @see \Rector\Tests\DowngradePhp72\Rector\ClassMethod\DowngradeParameterTypeWideningRector\DowngradeParameterTypeWideningRectorTest
 */
final class DowngradeParameterTypeWideningRector extends AbstractRector
{
    /**
     * Methods that are downgraded on a parent stack, by class, then method name
     * @var array<class-string, array<string, ClassMethod[]>>
     */
    private array $classMethodStack = [];

    public function __construct(
        private ParentChildClassMethodTypeResolver $parentChildClassMethodTypeResolver,
        private NativeParamToPhpDocDecorator $nativeParamToPhpDocDecorator,
        private TypeFactory $typeFactory,
        private ClassLikeScopeResolver $classLikeScopeResolver
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
        return [ClassMethod::class];
    }

    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        $scope = $this->classLikeScopeResolver->resolveScope($node);
        if (! $scope instanceof Scope) {
            return null;
        }

        $classReflection = $scope->getClassReflection();
        if (! $classReflection instanceof ClassReflection) {
            return null;
        }

        $classMethodName = $this->getName($node);

        // the method can be implemented (interface), or extended (class), but we don't know who implements it and how, so we'll put it on stack and get back to it, if one of the child class/interface appears to visit it :)

        $this->classMethodStack[$classReflection->getName()][$classMethodName][] = $node;

        if ($this->skipClassMethod($node)) {
            return null;
        }

        return $this->refactorClassMethod($node, $classReflection);
    }

    /**
     * The topmost class is the source of truth, so we go only down to avoid up/down collission
     */
    private function refactorClassMethod(ClassMethod $classMethod, ClassReflection $classReflection): ?ClassMethod
    {
        /** @var string $methodName */
        $methodName = $this->nodeNameResolver->getName($classMethod);

        $hasChanged = false;
        foreach (array_keys($classMethod->params) as $paramPosition) {
            $parameterTypesByParentClassLikes = $this->parentChildClassMethodTypeResolver->resolve(
                $classReflection,
                $methodName,
                $paramPosition,
                $this->classMethodStack
            );

            $uniqueTypes = $this->typeFactory->uniquateTypes($parameterTypesByParentClassLikes);

            // all methods from now to the top share the same param type → nothing to change for this parameter
            if (count($uniqueTypes) === 1) {
                continue;
            }

            $hasChanged = true;
            $this->removeParamTypeFromMethod($classMethod, $paramPosition);

            // update also all the ancestors in the stack
            foreach ($classReflection->getAncestors() as $ancestorClassReflection) {
                // skip self, because its handled directly here
                if ($ancestorClassReflection === $classReflection) {
                    continue;
                }

                $stackedClassMethods = $this->classMethodStack[$ancestorClassReflection->getName()][$methodName] ?? [];

                foreach ($stackedClassMethods as $stackedClassMethod) {
                    $this->removeParamTypeFromMethod($stackedClassMethod, $paramPosition);
                }
            }
        }

        return $hasChanged ? $classMethod : null;
    }

    private function removeParamTypeFromMethod(ClassMethod $classMethod, int $paramPosition): void
    {
        $param = $classMethod->params[$paramPosition] ?? null;
        if (! $param instanceof Param) {
            return;
        }

        // It already has no type => nothing to do - check original param, as it could have been removed by this rule
        $originalParam = $param->getAttribute(AttributeKey::ORIGINAL_NODE);
        if ($originalParam instanceof Param && $originalParam->type === null) {
            return;
        }
        if ($param->type === null) {
            return;
        }

        // Add the current type in the PHPDoc
        $this->nativeParamToPhpDocDecorator->decorate($classMethod, $param);
        $param->type = null;
    }

    private function skipClassMethod(ClassMethod $classMethod): bool
    {
        if ($classMethod->isMagic()) {
            return true;
        }

        return $classMethod->params === [];
    }
}
