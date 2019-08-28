<?php

declare(strict_types=1);

namespace Rector\Doctrine\Rector\Class_;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use Rector\Doctrine\Collector\EntitiesWithAddedUuidPropertyCollector;
use Rector\Doctrine\NodeFactory\EntityUuidNodeFactory;
use Rector\PhpParser\Node\Manipulator\ClassManipulator;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @sponsor Thanks https://spaceflow.io/ for sponsoring this rule - visit them on https://github.com/SpaceFlow-app
 */
final class AddUuidToEntityWhereMissingRector extends AbstractRector
{
    /**
     * @var EntityUuidNodeFactory
     */
    private $entityUuidNodeFactory;

    /**
     * @var ClassManipulator
     */
    private $classManipulator;

    /**
     * @var EntitiesWithAddedUuidPropertyCollector
     */
    private $entitiesWithAddedUuidPropertyCollector;

    public function __construct(
        EntityUuidNodeFactory $entityUuidNodeFactory,
        ClassManipulator $classManipulator,
        EntitiesWithAddedUuidPropertyCollector $entitiesWithAddedUuidPropertyCollector
    ) {
        $this->entityUuidNodeFactory = $entityUuidNodeFactory;
        $this->classManipulator = $classManipulator;
        $this->entitiesWithAddedUuidPropertyCollector = $entitiesWithAddedUuidPropertyCollector;
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
        if (! $this->isDoctrineEntityClass($node)) {
            return null;
        }

        // already has $uuid property
        if ($this->classManipulator->getProperty($node, 'uuid')) {
            return null;
        }

        if ($this->hasClassIdPropertyWithUuidType($node)) {
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
            $constructClassMethod = $this->entityUuidNodeFactory->createConstructorWithUuidPropertyDefaultValueAssign();
            $node->stmts = array_merge([$constructClassMethod], $node->stmts);
        }

        /** @var string $class */
        $class = $this->getName($node);
        $this->entitiesWithAddedUuidPropertyCollector->addClass($class);

        return $node;
    }

    private function hasClassIdPropertyWithUuidType(Class_ $class): bool
    {
        foreach ($class->stmts as $classStmt) {
            if (! $classStmt instanceof Property) {
                continue;
            }

            if (! $this->isName($classStmt, 'id')) {
                continue;
            }

            $propertyPhpDocInfo = $this->getPhpDocInfo($classStmt);
            if ($propertyPhpDocInfo === null) {
                return false;
            }

            $idTagValueNode = $propertyPhpDocInfo->getDoctrineIdTagValueNode();
            if ($idTagValueNode === null) {
                return false;
            }

            // get column!
            $columnTagValueNode = $propertyPhpDocInfo->getDoctrineColumnTagValueNode();
            if ($columnTagValueNode === null) {
                return false;
            }

            return (bool) Strings::match((string) $columnTagValueNode->getType(), '#^uuid(_binary)?$#');
        }

        return false;
    }
}
