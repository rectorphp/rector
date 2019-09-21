<?php declare(strict_types=1);

namespace Rector\Doctrine\PhpDocParser;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Property;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\DoctrinePhpDocParser\Ast\PhpDoc\Class_\EntityTagValueNode;
use Rector\DoctrinePhpDocParser\Ast\PhpDoc\Property_\ColumnTagValueNode;
use Rector\DoctrinePhpDocParser\Ast\PhpDoc\Property_\IdTagValueNode;
use Rector\DoctrinePhpDocParser\Contract\Ast\PhpDoc\DoctrineRelationTagValueNodeInterface;
use Rector\Exception\ShouldNotHappenException;
use Rector\NodeContainer\ParsedNodesByType;
use Rector\NodeTypeResolver\ClassExistenceStaticHelper;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockManipulator;
use ReflectionClass;

final class DoctrineDocBlockResolver
{
    /**
     * @var DocBlockManipulator
     */
    private $docBlockManipulator;

    /**
     * @var ParsedNodesByType
     */
    private $parsedNodesByType;

    public function __construct(DocBlockManipulator $docBlockManipulator, ParsedNodesByType $parsedNodesByType)
    {
        $this->docBlockManipulator = $docBlockManipulator;
        $this->parsedNodesByType = $parsedNodesByType;
    }

    /**
     * @param Class_|string $class
     */
    public function isDoctrineEntityClass($class): bool
    {
        if ($class instanceof Class_) {
            $classPhpDocInfo = $this->getPhpDocInfo($class);
            if ($classPhpDocInfo === null) {
                return false;
            }

            return (bool) $classPhpDocInfo->getByType(EntityTagValueNode::class);
        }

        if (is_string($class)) {
            if (ClassExistenceStaticHelper::doesClassLikeExist($class)) {
                $classNode = $this->parsedNodesByType->findClass($class);
                if ($classNode) {
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
        $propertyPhpDocInfo = $this->getPhpDocInfo($property);
        if ($propertyPhpDocInfo === null) {
            return false;
        }

        return (bool) $propertyPhpDocInfo->getByType(IdTagValueNode::class);
    }

    public function getDoctrineRelationTagValueNode(Property $property): ?DoctrineRelationTagValueNodeInterface
    {
        $propertyPhpDocInfo = $this->getPhpDocInfo($property);
        if ($propertyPhpDocInfo === null) {
            return null;
        }

        return $propertyPhpDocInfo->getByType(DoctrineRelationTagValueNodeInterface::class);
    }

    public function isDoctrineProperty(Property $property): bool
    {
        $propertyPhpDocInfo = $this->getPhpDocInfo($property);
        if ($propertyPhpDocInfo === null) {
            return false;
        }

        if ($propertyPhpDocInfo->getByType(ColumnTagValueNode::class)) {
            return true;
        }

        return (bool) $propertyPhpDocInfo->getByType(DoctrineRelationTagValueNodeInterface::class);
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

    private function getPhpDocInfo(Node $node): ?PhpDocInfo
    {
        if ($node->getDocComment() === null) {
            return null;
        }

        return $this->docBlockManipulator->createPhpDocInfoFromNode($node);
    }
}
