<?php

declare(strict_types=1);

namespace Rector\Doctrine\Provider;

use Nette\Utils\Strings;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Property_\ColumnTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Property_\IdTagValueNode;
use Rector\Doctrine\PhpDocParser\DoctrineDocBlockResolver;
use Rector\NodeContainer\ParsedNodesByType;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpParser\Node\Manipulator\ClassManipulator;
use Rector\PhpParser\Node\Resolver\NameResolver;

final class EntityWithMissingUuidProvider
{
    /**
     * @var Class_[]
     */
    private $entitiesWithMissingUuidProperty = [];

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

    public function __construct(
        ParsedNodesByType $parsedNodesByType,
        DoctrineDocBlockResolver $doctrineDocBlockResolver,
        ClassManipulator $classManipulator,
        NameResolver $nameResolver
    ) {
        $this->parsedNodesByType = $parsedNodesByType;
        $this->doctrineDocBlockResolver = $doctrineDocBlockResolver;
        $this->classManipulator = $classManipulator;
        $this->nameResolver = $nameResolver;
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
            if ($this->classManipulator->getProperty($class, 'uuid') !== null) {
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
        /** @var PhpDocInfo $propertyPhpDocInfo */
        $propertyPhpDocInfo = $property->getAttribute(AttributeKey::PHP_DOC_INFO);
        if (! $propertyPhpDocInfo->hasByType(IdTagValueNode::class)) {
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
