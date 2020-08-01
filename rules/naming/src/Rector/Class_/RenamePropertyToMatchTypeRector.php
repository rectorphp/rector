<?php

declare(strict_types=1);

namespace Rector\Naming\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\VarLikeIdentifier;
use Rector\BetterPhpDocParser\PhpDocManipulator\PropertyDocBlockManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Naming\Guard\BreakingVariableRenameGuard;
use Rector\Naming\Naming\ConflictingNameResolver;
use Rector\Naming\Naming\ExpectedNameResolver;
use Rector\Naming\VariableRenamer;

/**
 * @see \Rector\Naming\Tests\Rector\Class_\RenamePropertyToMatchTypeRector\RenamePropertyToMatchTypeRectorTest
 */
final class RenamePropertyToMatchTypeRector extends AbstractRector
{
    /**
     * @var bool
     */
    private $hasChange = false;

    /**
     * @var ConflictingNameResolver
     */
    private $conflictingNameResolver;

    /**
     * @var ExpectedNameResolver
     */
    private $expectedNameResolver;

    /**
     * @var BreakingVariableRenameGuard
     */
    private $breakingVariableRenameGuard;

    /**
     * @var PropertyDocBlockManipulator
     */
    private $propertyDocBlockManipulator;

    /**
     * @var VariableRenamer
     */
    private $variableRenamer;

    public function __construct(
        BreakingVariableRenameGuard $breakingVariableRenameGuard,
        ConflictingNameResolver $conflictingNameResolver,
        ExpectedNameResolver $expectedNameResolver,
        PropertyDocBlockManipulator $propertyDocBlockManipulator,
        VariableRenamer $variableRenamer
    ) {
        $this->conflictingNameResolver = $conflictingNameResolver;
        $this->expectedNameResolver = $expectedNameResolver;
        $this->breakingVariableRenameGuard = $breakingVariableRenameGuard;
        $this->propertyDocBlockManipulator = $propertyDocBlockManipulator;
        $this->variableRenamer = $variableRenamer;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Rename property and method param to match its type', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    /**
     * @var EntityManager
     */
    private $eventManager;

    public function __construct(EntityManager $eventManager)
    {
        $this->eventManager = $eventManager;
    }
}
PHP
,
                <<<'PHP'
class SomeClass
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }
}
PHP
            ),
        ]);
    }

    /**
     * @return string[]
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
        $this->refactorClassMethods($node);
        $this->refactorClassProperties($node);

        if (! $this->hasChange) {
            return null;
        }

        return $node;
    }

    private function refactorClassMethods(ClassLike $classLike): void
    {
        foreach ($classLike->getMethods() as $classMethod) {
            $this->refactorClassMethod($classMethod);
        }
    }

    private function refactorClassProperties(ClassLike $classLike): void
    {
        $conflictingPropertyNames = $this->conflictingNameResolver->resolveConflictingPropertyNames($classLike);

        foreach ($classLike->getProperties() as $property) {
            if (count($property->props) !== 1) {
                continue;
            }

            /** @var string $oldName */
            $oldName = $this->getName($property);
            $expectedName = $this->expectedNameResolver->resolveForPropertyIfNotYet($property);
            if ($expectedName === null) {
                continue;
            }

            if ($this->shouldSkipPropertyRename($property, $oldName, $expectedName, $conflictingPropertyNames)) {
                continue;
            }

            $onlyPropertyProperty = $property->props[0];
            $onlyPropertyProperty->name = new VarLikeIdentifier($expectedName);
            $this->renamePropertyFetchesInClass($classLike, $oldName, $expectedName);

            $this->hasChange = true;
        }
    }

    private function renamePropertyFetchesInClass(ClassLike $classLike, string $oldName, string $expectedName): void
    {
        // 1. replace property fetch rename in whole class
        $this->traverseNodesWithCallable([$classLike], function (Node $node) use ($oldName, $expectedName) {
            if (! $this->isLocalPropertyFetchNamed($node, $oldName)) {
                return null;
            }

            $node->name = new Identifier($expectedName);
            return $node;
        });
    }

    /**
     * @param string[] $conflictingPropertyNames
     */
    private function shouldSkipPropertyRename(
        Property $property,
        string $currentName,
        string $expectedName,
        array $conflictingPropertyNames
    ): bool {
        if (in_array($expectedName, $conflictingPropertyNames, true)) {
            return true;
        }

        return $this->breakingVariableRenameGuard->shouldSkipProperty($property, $currentName);
    }

    private function refactorClassMethod(ClassMethod $classMethod): void
    {
        foreach ($classMethod->params as $param) {
            $expectedName = $this->expectedNameResolver->resolveForParamIfNotYet($param);
            if ($expectedName === null) {
                continue;
            }

            /** @var string $paramName */
            $paramName = $this->getName($param);

            if ($this->breakingVariableRenameGuard->shouldSkipParam(
                $paramName,
                $expectedName,
                $classMethod,
                $param
            )) {
                continue;
            }

            // 1. rename param
            /** @var string $oldName */
            $oldName = $this->getName($param->var);
            $param->var->name = new Identifier($expectedName);

            // 2. rename param in the rest of the method
            $this->variableRenamer->renameVariableInFunctionLike($classMethod, null, $oldName, $expectedName);

//            $this->renameVariableInClassMethod($classMethod, $oldName, $expectedName);

            // 3. rename @param variable in docblock too
            $this->propertyDocBlockManipulator->renameParameterNameInDocBlock($classMethod, $oldName, $expectedName);

            $this->hasChange = true;
        }
    }
}
