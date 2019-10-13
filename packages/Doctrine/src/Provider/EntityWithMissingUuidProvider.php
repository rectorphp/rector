<?php

declare(strict_types=1);

namespace Rector\Doctrine\Provider;

use Nette\Utils\Strings;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Property_\ColumnTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Property_\IdTagValueNode;
use Rector\Doctrine\PhpDocParser\DoctrineDocBlockResolver;
use Rector\NodeContainer\ParsedNodesByType;
use Rector\PhpParser\Node\Manipulator\ClassManipulator;
use Rector\PhpParser\Node\Resolver\NameResolver;

final class EntityWithMissingUuidProvider
{
    /**
     * @var ParsedNodesByType
     */
    private $parsedNodesByType;

    /**
     * @var DoctrineDocBlockResolver
     */
    private $doctrineDocBlockResolver;

    /**
     * @var ClassManipulator
     */
    private $classManipulator;

    /**
     * @var NameResolver
     */
    private $nameResolver;

    /**
     * @var PhpDocInfoFactory
     */
    private $phpDocInfoFactory;

    /**
     * @var Class_[]
     */
    private $entitiesWithMissingUuidProperty = [];

    public function __construct(
        ParsedNodesByType $parsedNodesByType,
        DoctrineDocBlockResolver $doctrineDocBlockResolver,
        ClassManipulator $classManipulator,
        NameResolver $nameResolver,
        PhpDocInfoFactory $phpDocInfoFactory
    ) {
        $this->parsedNodesByType = $parsedNodesByType;
        $this->doctrineDocBlockResolver = $doctrineDocBlockResolver;
        $this->classManipulator = $classManipulator;
        $this->nameResolver = $nameResolver;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
    }

    /**
     * @return Class_[]
     */
    public function provide(): array
    {
        if ($this->entitiesWithMissingUuidProperty !== [] && ! defined('RECTOR_REPOSITORY')) {
            return $this->entitiesWithMissingUuidProperty;
        }

        $entitiesWithMissingUuidProperty = [];
        foreach ($this->parsedNodesByType->getClasses() as $class) {
            if (! $this->doctrineDocBlockResolver->isDoctrineEntityClassWithIdProperty($class)) {
                continue;
            }

            // already has $uuid property
            if ($this->classManipulator->getProperty($class, 'uuid')) {
                continue;
            }

            if ($this->hasClassIdPropertyWithUuidType($class)) {
                continue;
            }

            $entitiesWithMissingUuidProperty[] = $class;
        }

        $this->entitiesWithMissingUuidProperty = $entitiesWithMissingUuidProperty;

        return $entitiesWithMissingUuidProperty;
    }

    private function hasClassIdPropertyWithUuidType(Class_ $class): bool
    {
        foreach ($class->stmts as $classStmt) {
            if (! $classStmt instanceof Property) {
                continue;
            }

            if (! $this->nameResolver->isName($classStmt, 'id')) {
                continue;
            }

            return $this->isPropertyClassIdWithUuidType($classStmt);
        }

        return false;
    }

    private function isPropertyClassIdWithUuidType(Property $property): bool
    {
        $propertyPhpDocInfo = $this->phpDocInfoFactory->createFromNode($property);

        $idTagValueNode = $propertyPhpDocInfo->getByType(IdTagValueNode::class);
        if ($idTagValueNode === null) {
            return false;
        }

        $columnTagValueNode = $propertyPhpDocInfo->getByType(ColumnTagValueNode::class);
        if ($columnTagValueNode === null) {
            return false;
        }

        if ($columnTagValueNode->getType() === null) {
            return false;
        }

        return (bool) Strings::match($columnTagValueNode->getType(), '#^uuid(_binary)?$#');
    }
}
