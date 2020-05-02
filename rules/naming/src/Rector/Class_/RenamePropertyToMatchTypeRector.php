<?php

declare(strict_types=1);

namespace Rector\Naming\Rector\Class_;

use Nette\Utils\Strings;
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
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Naming\PropertyNaming;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see \Rector\Naming\Tests\Rector\Class_\RenamePropertyToMatchTypeRector\RenamePropertyToMatchTypeRectorTest
 */
final class RenamePropertyToMatchTypeRector extends AbstractRector
{
    /**
     * @var PropertyNaming
     */
    private $propertyNaming;

    public function __construct(PropertyNaming $propertyNaming)
    {
        $this->propertyNaming = $propertyNaming;
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
        $this->refactorClassMethods((array) $node->getMethods());
        $this->refactorClassProperties((array) $node->getProperties(), $node);

        return $node;
    }

    private function matchExpectedParamNameIfNotYet(Param $param): ?string
    {
        // nothing to verify
        if ($param->type === null) {
            return null;
        }

        $staticType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($param->type);
        $expectedName = $this->propertyNaming->getExpectedNameFromType($staticType);
        if ($expectedName === null) {
            return null;
        }

        /** @var string $currentName */
        $currentName = $this->getName($param->var);
        if ($currentName === $expectedName) {
            return null;
        }

        if ($this->endsWith($currentName, $expectedName)) {
            return null;
        }

        return $expectedName;
    }

    /**
     * Ends with ucname
     * Starts with adjective, e.g. (Post $firstPost, Post $secondPost)
     */
    private function endsWith(string $currentName, string $expectedName): bool
    {
        return (bool) Strings::match($currentName, '#\w+' . lcfirst($expectedName) . '#');
    }

    /**
     * @param ClassMethod[] $classMethods
     */
    private function refactorClassMethods(array $classMethods): void
    {
        foreach ($classMethods as $classMethod) {
            $conflictingNames = $this->resolveConflictingNamesFromClassMethod($classMethod);

            foreach ($classMethod->params as $param) {
                $expectedName = $this->matchExpectedParamNameIfNotYet($param);
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

    /**
     * @param Property[] $properties
     */
    private function refactorClassProperties(array $properties, ClassLike $classLike): void
    {
        $conflictingPropertyNames = $this->resolveConflictingPropertyNamesFromClass($classLike);

        foreach ($properties as $property) {
            if (count($property->props) !== 1) {
                continue;
            }

            $oldName = $this->getName($property);
            $expectedName = $this->matchExpectedPropertyNameIfNotYet($property);
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

    private function matchExpectedPropertyNameIfNotYet(Property $property): ?string
    {
        $currentName = $this->getName($property);

        /** @var PhpDocInfo|null $phpDocInfo */
        $phpDocInfo = $property->getAttribute(AttributeKey::PHP_DOC_INFO);
        $expectedName = $this->propertyNaming->getExpectedNameFromType($phpDocInfo->getVarType());

        if ($expectedName === $currentName) {
            return null;
        }

        return $expectedName;
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

    /**
     * @return string[]
     */
    private function resolveConflictingNamesFromClassMethod(ClassMethod $classMethod): array
    {
        $expectedNames = [];
        foreach ($classMethod->params as $param) {
            $expectedName = $this->matchExpectedParamNameIfNotYet($param);
            if ($expectedName === null) {
                continue;
            }

            $expectedNames[] = $expectedName;
        }

        $expectedNamesToCount = array_count_values($expectedNames);

        $conflictingExpectedNames = [];
        foreach ($expectedNamesToCount as $expectedName => $count) {
            if ($count >= 2) {
                $conflictingExpectedNames[] = $expectedName;
            }
        }

        return $conflictingExpectedNames;
    }

    /**
     * @return string[]
     */
    private function resolveConflictingPropertyNamesFromClass(ClassLike $classLike): array
    {
        $expectedNames = [];
        foreach ($classLike->getProperties() as $property) {
            $expectedName = $this->matchExpectedPropertyNameIfNotYet($property);
            if ($expectedName === null) {
                continue;
            }

            $expectedNames[] = $expectedName;
        }

        $expectedNamesToCount = array_count_values($expectedNames);

        $conflictingExpectedNames = [];
        foreach ($expectedNamesToCount as $expectedName => $count) {
            if ($count >= 2) {
                $conflictingExpectedNames[] = $expectedName;
            }
        }

        return $conflictingExpectedNames;
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
