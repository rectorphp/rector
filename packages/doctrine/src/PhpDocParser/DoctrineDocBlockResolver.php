<?php

declare(strict_types=1);

namespace Rector\Doctrine\PhpDocParser;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Property;
use Rector\BetterPhpDocParser\Contract\Doctrine\DoctrineRelationTagValueNodeInterface;
use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Class_\EntityTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Property_\ColumnTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Property_\IdTagValueNode;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\NodeContainer\NodeCollector\ParsedNodeCollector;
use Rector\NodeTypeResolver\ClassExistenceStaticHelper;
use Rector\NodeTypeResolver\Node\AttributeKey;
use ReflectionClass;

final class DoctrineDocBlockResolver
{
    /**
     * @var ParsedNodeCollector
     */
    private $parsedNodeCollector;

    public function __construct(ParsedNodeCollector $parsedNodeCollector)
    {
        $this->parsedNodeCollector = $parsedNodeCollector;
    }

    /**
     * @param Class_|string|mixed $class
     */
    public function isDoctrineEntityClass($class): bool
    {
        if ($class instanceof Class_) {
            $phpDocInfo = $class->getAttribute(AttributeKey::PHP_DOC_INFO);
            if ($phpDocInfo === null) {
                return false;
            }

            return $phpDocInfo->hasByType(EntityTagValueNode::class);
        }

        if (is_string($class)) {
            if (ClassExistenceStaticHelper::doesClassLikeExist($class)) {
                $classNode = $this->parsedNodeCollector->findClass($class);
                if ($classNode !== null) {
                    return $this->isDoctrineEntityClass($classNode);
                }

                $reflectionClass = new ReflectionClass($class);

                // dummy check of 3rd party code without running it
                return Strings::contains((string) $reflectionClass->getDocComment(), '@ORM\Entity');
            }

            return false;
        }

        throw new ShouldNotHappenException();
    }

    public function isDoctrineEntityClassWithIdProperty(Class_ $class): bool
    {
        if (! $this->isDoctrineEntityClass($class)) {
            return false;
        }

        foreach ($class->stmts as $classStmt) {
            if (! $classStmt instanceof Property) {
                continue;
            }

            if ($this->hasPropertyDoctrineIdTag($classStmt)) {
                return true;
            }
        }

        return false;
    }

    public function getTargetEntity(Property $property): ?string
    {
        $doctrineRelationTagValueNode = $this->getDoctrineRelationTagValueNode($property);
        if ($doctrineRelationTagValueNode === null) {
            return null;
        }

        return $doctrineRelationTagValueNode->getTargetEntity();
    }

    public function hasPropertyDoctrineIdTag(Property $property): bool
    {
        $phpDocInfo = $property->getAttribute(AttributeKey::PHP_DOC_INFO);
        if ($phpDocInfo === null) {
            return false;
        }

        return $phpDocInfo->hasByType(IdTagValueNode::class);
    }

    public function getDoctrineRelationTagValueNode(Property $property): ?DoctrineRelationTagValueNodeInterface
    {
        $phpDocInfo = $property->getAttribute(AttributeKey::PHP_DOC_INFO);
        if ($phpDocInfo === null) {
            return null;
        }

        return $phpDocInfo->getByType(DoctrineRelationTagValueNodeInterface::class);
    }

    public function isDoctrineProperty(Property $property): bool
    {
        $phpDocInfo = $property->getAttribute(AttributeKey::PHP_DOC_INFO);
        if ($phpDocInfo === null) {
            return false;
        }

        if ($phpDocInfo->hasByType(ColumnTagValueNode::class)) {
            return true;
        }

        return $phpDocInfo->hasByType(DoctrineRelationTagValueNodeInterface::class);
    }

    public function isInDoctrineEntityClass(Node $node): bool
    {
        /** @var ClassLike|null $classNode */
        $classNode = $node->getAttribute(AttributeKey::CLASS_NODE);
        if (! $classNode instanceof Class_) {
            return false;
        }

        return $this->isDoctrineEntityClass($classNode);
    }
}
