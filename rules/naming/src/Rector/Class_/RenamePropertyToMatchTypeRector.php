<?php

declare(strict_types=1);

namespace Rector\Naming\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Identifier;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\VarLikeIdentifier;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Naming\Guard\BreakingVariableRenameGuard;
use Rector\Naming\Naming\ConflictingNameResolver;
use Rector\Naming\Naming\ExpectedNameResolver;

/**
 * @see \Rector\Naming\Tests\Rector\Class_\RenamePropertyToMatchTypeRector\RenamePropertyToMatchTypeRectorTest
 */
final class RenamePropertyToMatchTypeRector extends AbstractRector
{
    /**
     * @var bool
     */
    private $hasChanged = false;

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

    public function __construct(
        BreakingVariableRenameGuard $breakingVariableRenameGuard,
        ConflictingNameResolver $conflictingNameResolver,
        ExpectedNameResolver $expectedNameResolver
    ) {
        $this->conflictingNameResolver = $conflictingNameResolver;
        $this->expectedNameResolver = $expectedNameResolver;
        $this->breakingVariableRenameGuard = $breakingVariableRenameGuard;
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
        $this->refactorClassProperties($node);

        if (! $this->hasChanged) {
            return null;
        }

        return $node;
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

            if ($this->shouldSkipProperty($property, $oldName, $expectedName, $conflictingPropertyNames)) {
                continue;
            }

            $onlyPropertyProperty = $property->props[0];
            $onlyPropertyProperty->name = new VarLikeIdentifier($expectedName);
            $this->renamePropertyFetchesInClass($classLike, $oldName, $expectedName);

            $this->hasChanged = true;
        }
    }

    private function shouldSkipProperty(
        Property $property,
        string $oldName,
        string $expectedName,
        array $conflictingPropertyNames
    ): bool {
        if ($this->isObjectType($property, 'Ramsey\Uuid\UuidInterface')) {
            return true;
        }

        return $this->shouldSkipPropertyRename($property, $oldName, $expectedName, $conflictingPropertyNames);
    }

    private function renamePropertyFetchesInClass(ClassLike $classLike, string $oldName, string $expectedName): void
    {
        // 1. replace property fetch rename in whole class
        $this->traverseNodesWithCallable([$classLike], function (Node $node) use ($oldName, $expectedName): ?\PhpParser\Node\Expr\PropertyFetch {
            if (! $this->isLocalPropertyFetchNamed($node, $oldName)) {
                return null;
            }

            /** @var PropertyFetch $node */
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
}
