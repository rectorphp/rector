<?php

declare(strict_types=1);

namespace Rector\Naming\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Interface_;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Naming\ConflictingNameResolver\PropertyConflictingNameResolver;
use Rector\Naming\ExpectedNameResolver\FromPropertyTypeExpectedNameResolver;
use Rector\Naming\PropertyRenamer;
use Rector\Naming\ValueObjectFactory\PropertyRenameFactory;

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
     * @var PropertyRenamer
     */
    private $propertyRenamer;

    /**
     * @var PropertyRenameFactory
     */
    private $propertyRenameFactory;

    public function __construct(
        PropertyRenamer $propertyRenamer,
        FromPropertyTypeExpectedNameResolver $fromPropertyTypeExpectedNameResolver,
        PropertyConflictingNameResolver $propertyConflictingNameResolver,
        PropertyRenameFactory $propertyRenameFactory
    ) {
        $propertyConflictingNameResolver->setExpectedNameResolver($fromPropertyTypeExpectedNameResolver);

        $this->propertyRenamer = $propertyRenamer;
        $this->propertyRenamer->setConflictingNameResolver($propertyConflictingNameResolver);

        $this->propertyRenameFactory = $propertyRenameFactory;
        $this->propertyRenameFactory->setExpectedNameResolver($fromPropertyTypeExpectedNameResolver);
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
            $propertyRename = $this->propertyRenameFactory->create($property);
            if ($propertyRename === null) {
                continue;
            }

            if ($this->propertyRenamer->rename($propertyRename) !== null) {
                $this->hasChanged = true;
            }
        }
    }
}
