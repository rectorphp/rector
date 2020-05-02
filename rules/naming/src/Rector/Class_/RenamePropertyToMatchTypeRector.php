<?php

declare(strict_types=1);

namespace Rector\Naming\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Identifier;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\VarLikeIdentifier;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Naming\Naming\ConflictingNameResolver;
use Rector\Naming\Naming\ExpectedNameResolver;

/**
 * @see \Rector\Naming\Tests\Rector\Class_\RenamePropertyToMatchTypeRector\RenamePropertyToMatchTypeRectorTest
 */
final class RenamePropertyToMatchTypeRector extends AbstractRector
{
    /**
     * @var ConflictingNameResolver
     */
    private $conflictingNameResolver;

    /**
     * @var ExpectedNameResolver
     */
    private $expectedNameResolver;

    public function __construct(
        ConflictingNameResolver $conflictingNameResolver,
        ExpectedNameResolver $expectedNameResolver
    ) {
        $this->conflictingNameResolver = $conflictingNameResolver;
        $this->expectedNameResolver = $expectedNameResolver;
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

        return $node;
    }

    private function refactorClassMethods(ClassLike $classLike): void
    {
        foreach ($classLike->getMethods() as $classMethod) {
            $conflictingNames = $this->conflictingNameResolver->resolveConflictingVariableNames($classMethod);

            foreach ($classMethod->params as $param) {
                $expectedName = $this->expectedNameResolver->resolveForParam($param);
                if ($expectedName === null) {
                    continue;
                }

                if (in_array($expectedName, $conflictingNames, true)) {
                    continue;
                }

                // 1. rename param
                /** @var string $oldName */
                $oldName = $this->getName($param->var);
                $param->var->name = new Identifier($expectedName);

                // 2. rename param in the rest of the method
                $this->renameVariableInClassMethod($classMethod, $oldName, $expectedName);
            }
        }
    }

    private function refactorClassProperties(ClassLike $classLike): void
    {
        $conflictingPropertyNames = $this->conflictingNameResolver->resolveConflictingPropertyNames($classLike);

        foreach ($classLike->getProperties() as $property) {
            if (count($property->props) !== 1) {
                continue;
            }

            $oldName = $this->getName($property);
            $expectedName = $this->expectedNameResolver->resolveForProperty($property);
            if ($expectedName === null) {
                continue;
            }

            // skip conflicting
            if (in_array($expectedName, $conflictingPropertyNames, true)) {
                continue;
            }

            $onlyPropertyProperty = $property->props[0];
            $onlyPropertyProperty->name = new VarLikeIdentifier($expectedName);
            $this->renamePropertyFetchesInClass($classLike, $oldName, $expectedName);
        }
    }

    private function renameVariableInClassMethod(ClassMethod $classMethod, string $oldName, string $expectedName): void
    {
        $this->traverseNodesWithCallable((array) $classMethod->stmts, function (Node $node) use (
            $oldName,
            $expectedName
        ) {
            if (! $this->isVariableName($node, $oldName)) {
                return null;
            }

            $node->name = new Identifier($expectedName);
            return $node;
        });
    }

    private function renamePropertyFetchesInClass(ClassLike $classLike, ?string $oldName, string $expectedName): void
    {
        // 1. replace property fetch rename in whole class
        $this->traverseNodesWithCallable([$classLike], function (Node $node) use ($oldName, $expectedName) {
            if (! $node instanceof PropertyFetch) {
                return null;
            }

            // local property
            if (! $this->isVariableName($node->var, 'this')) {
                return null;
            }

            if (! $this->isName($node->name, $oldName)) {
                return null;
            }

            $node->name = new Identifier($expectedName);
            return $node;
        });
    }
}
