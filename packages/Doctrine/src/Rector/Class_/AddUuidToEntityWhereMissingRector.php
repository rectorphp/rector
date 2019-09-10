<?php

declare(strict_types=1);

namespace Rector\Doctrine\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Rector\Doctrine\Collector\UuidMigrationDataCollector;
use Rector\Doctrine\NodeFactory\EntityUuidNodeFactory;
use Rector\Doctrine\Provider\EntityWithMissingUuidProvider;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @sponsor Thanks https://spaceflow.io/ for sponsoring this rule - visit them on https://github.com/SpaceFlow-app
 * @see \Rector\Doctrine\Tests\Rector\Class_\AddUuidToEntityWhereMissingRector\AddUuidToEntityWhereMissingRectorTest
 */
final class AddUuidToEntityWhereMissingRector extends AbstractRector
{
    /**
     * @var EntityUuidNodeFactory
     */
    private $entityUuidNodeFactory;

    /**
     * @var UuidMigrationDataCollector
     */
    private $uuidMigrationDataCollector;

    /**
     * @var EntityWithMissingUuidProvider
     */
    private $entityWithMissingUuidProvider;

    public function __construct(
        EntityUuidNodeFactory $entityUuidNodeFactory,
        UuidMigrationDataCollector $uuidMigrationDataCollector,
        EntityWithMissingUuidProvider $entityWithMissingUuidProvider
    ) {
        $this->entityUuidNodeFactory = $entityUuidNodeFactory;
        $this->uuidMigrationDataCollector = $uuidMigrationDataCollector;
        $this->entityWithMissingUuidProvider = $entityWithMissingUuidProvider;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Adds $uuid property to entities, that already have $id with integer type.' .
            'Require for step-by-step migration from int to uuid. ' .
            'In following step it should be renamed to $id and replace it'
        );
    }

    /**
     * @return string[]
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
        $entitiesWithMissingUuidProperty = $this->entityWithMissingUuidProvider->provide();
        if (! in_array($node, $entitiesWithMissingUuidProperty, true)) {
            return null;
        }

        // 1. add to start of the class, so it can be easily seen
        $uuidProperty = $this->entityUuidNodeFactory->createTemporaryUuidProperty();
        $node->stmts = array_merge([$uuidProperty], $node->stmts);

        // 2. add default value to uuid property
        $constructClassMethod = $node->getMethod('__construct');
        if ($constructClassMethod) {
            $assignExpression = $this->entityUuidNodeFactory->createUuidPropertyDefaultValueAssign();
            $constructClassMethod->stmts = array_merge([$assignExpression], (array) $constructClassMethod->stmts);
        } else {
            $constructClassMethod = $this->entityUuidNodeFactory->createConstructorWithUuidInitialization($node);
            $node->stmts = array_merge([$constructClassMethod], $node->stmts);
        }

        /** @var string $class */
        $class = $this->getName($node);
        $this->uuidMigrationDataCollector->addClassAndProperty($class, 'uuid');

        return $node;
    }
}
