<?php

declare(strict_types=1);

namespace Rector\Naming\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Interface_;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Naming\Naming\ExpectedNameResolver;
use Rector\Naming\PropertyRenamer;
use Rector\Naming\ValueObject\PropertyRename;
use Rector\NodeTypeResolver\Node\AttributeKey;

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
     * @var ExpectedNameResolver
     */
    private $expectedNameResolver;

    /**
     * @var PropertyRenamer
     */
    private $propertyRenamer;

    public function __construct(ExpectedNameResolver $expectedNameResolver, PropertyRenamer $propertyRenamer)
    {
        $this->expectedNameResolver = $expectedNameResolver;
        $this->propertyRenamer = $propertyRenamer;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Rename property and method param to match its type', [
            new CodeSample(
                <<<'CODE_SAMPLE'
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
CODE_SAMPLE
,
                <<<'CODE_SAMPLE'
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
CODE_SAMPLE
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
        foreach ($classLike->getProperties() as $property) {
            if (count($property->props) !== 1) {
                continue;
            }

            $expectedName = $this->expectedNameResolver->resolveForPropertyIfNotYet($property);
            if ($expectedName === null) {
                continue;
            }

            $currentName = $this->getName($property);
            $propertyType = $this->getObjectType($property);
            $propertyClassLike = $property->getAttribute(AttributeKey::CLASS_NODE);
            if ($propertyClassLike === null) {
                throw new ShouldNotHappenException("There shouldn't be a property without Class Node");
            }

            $propertyRename = new PropertyRename(
                $property,
                $expectedName,
                $currentName,
                $propertyType,
                $propertyClassLike
            );

            if ($this->propertyRenamer->rename($propertyRename)) {
                continue;
            }

            $this->hasChanged = true;
        }
    }
}
