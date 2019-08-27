<?php

declare(strict_types=1);

namespace Rector\Doctrine\Rector\Class_;

use Nette\Utils\Strings;
use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\PropertyProperty;
use PhpParser\Node\VarLikeIdentifier;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use Rector\BetterPhpDocParser\Attributes\Ast\PhpDoc\AttributeAwarePhpDocTagNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\DoctrinePhpDocParser\Ast\PhpDoc\Property_\JoinColumnTagValueNode;
use Rector\DoctrinePhpDocParser\Ast\PhpDoc\Property_\JoinTableTagValueNode;
use Rector\DoctrinePhpDocParser\Contract\Ast\PhpDoc\DoctrineRelationTagValueNodeInterface;
use Rector\DoctrinePhpDocParser\Contract\Ast\PhpDoc\ToManyTagNodeInterface;
use Rector\DoctrinePhpDocParser\Contract\Ast\PhpDoc\ToOneTagNodeInterface;
use Rector\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockManipulator;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @sponsor Thanks https://spaceflow.io/ for sponsoring this rule - visit them on https://github.com/SpaceFlow-app
 */
final class AddUuidMirrorForRelationPropertyRector extends AbstractRector
{
    /**
     * @var DocBlockManipulator
     */
    private $docBlockManipulator;

    public function __construct(DocBlockManipulator $docBlockManipulator)
    {
        $this->docBlockManipulator = $docBlockManipulator;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Adds $uuid property to entities, that already have $id with integer type.' .
            'Require for step-by-step migration from int to uuid.'
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

        // traverse relations and see which of them have freshly added uuid on the other side
        foreach ($node->stmts as $classStmt) {
            if (! $classStmt instanceof Property) {
                continue;
            }

            // this relation already is or has uuid property
            if ($this->isName($classStmt, '*Uuid')) {
                continue;
            }

            $uuidPropertyName = $this->getName($classStmt) . 'Uuid';

            if ($this->hasClassPropertyName($node, $uuidPropertyName)) {
                continue;
            }

            $doctrineRelationTagValueNode = $this->getDoctrineRelationTagValueNode($classStmt);
            if ($doctrineRelationTagValueNode === null) {
                continue;
            }

            if ($doctrineRelationTagValueNode->getTargetEntity() === null) {
                throw new ShouldNotHappenException();
            }

            $node->stmts[] = $this->createMirrorNullable($classStmt);
        }

        return $node;
    }

    /**
     * Creates duplicated property, that has "*uuidSuffix"
     * and nullable join column, so we cna complete them manually
     */
    private function createMirrorNullable(Property $property): Property
    {
        $propertyWithUuid = clone $property;

        // this is needed to keep old property name
        $this->updateDocComment($propertyWithUuid);

        // name must be changed after the doc comment update, because the reflection annotation needed for update of doc comment
        // would miss non existing *Uuid property
        $uuidPropertyName = $this->getName($propertyWithUuid) . 'Uuid';
        $newPropertyProperty = new PropertyProperty(new VarLikeIdentifier($uuidPropertyName));
        $propertyWithUuid->props = [$newPropertyProperty];

        return $propertyWithUuid;
    }

    /**
     * Creates unique many-to-many table name like: first_table_uuid_second_table_uuid
     */
    private function createManyToManyUuidTableName(Property $property): string
    {
        /** @var string $currentClass */
        $currentClass = $property->getAttribute(AttributeKey::CLASS_NAME);
        $shortCurrentClass = Strings::after($currentClass, '\\', -1);

        $targetClass = $this->resolveTargetClass($property);
        if ($targetClass === null) {
            throw new ShouldNotHappenException(__METHOD__);
        }

        $shortTargetClass = Strings::after($targetClass, '\\', -1);

        return strtolower($shortCurrentClass . '_uuid_' . $shortTargetClass . '_uuid');
    }

    private function resolveTargetClass(Property $property): ?string
    {
        /** @var PhpDocInfo $phpDocInfo */
        $phpDocInfo = $this->getPhpDocInfo($property);

        /** @var DoctrineRelationTagValueNodeInterface $relationTagValueNode */
        $relationTagValueNode = $phpDocInfo->getDoctrineRelationTagValueNode();

        return $relationTagValueNode->getFqnTargetEntity();
    }

    private function updateDocComment(Property $property): void
    {
        $propertyPhpDocInfo = $this->getPhpDocInfo($property);
        if ($propertyPhpDocInfo === null) {
            return;
        }

        /** @var DoctrineRelationTagValueNodeInterface $doctrineRelationTagValueNode */
        $doctrineRelationTagValueNode = $propertyPhpDocInfo->getDoctrineRelationTagValueNode();

        if ($doctrineRelationTagValueNode instanceof ToManyTagNodeInterface) {
            $this->refactorToManyPropertyPhpDocInfo($propertyPhpDocInfo, $property);
        } elseif ($doctrineRelationTagValueNode instanceof ToOneTagNodeInterface) {
            $this->refactorToOnePropertyPhpDocInfo($propertyPhpDocInfo);
        }

        $this->docBlockManipulator->updateNodeWithPhpDocInfo($property, $propertyPhpDocInfo);
    }

    private function refactorToManyPropertyPhpDocInfo(PhpDocInfo $propertyPhpDocInfo, Property $property): void
    {
        $doctrineJoinColumnTagValueNode = $propertyPhpDocInfo->getDoctrineJoinColumnTagValueNode();

        if ($doctrineJoinColumnTagValueNode) {
            // replace @ORM\JoinColumn with @ORM\JoinTable
            $propertyPhpDocInfo->removeTagValueNodeFromNode($doctrineJoinColumnTagValueNode);
        }

        $propertyPhpDocInfo->getPhpDocNode()->children[] = $this->createJoinTableTagNode($property);
    }

    private function refactorToOnePropertyPhpDocInfo(PhpDocInfo $propertyPhpDocInfo): void
    {
        $joinColumnTagValueNode = $propertyPhpDocInfo->getDoctrineJoinColumnTagValueNode();

        if ($joinColumnTagValueNode) {
            $joinColumnTagValueNode->changeNullable(true);
            $joinColumnTagValueNode->changeReferencedColumnName('uuid');
        } else {
            $propertyPhpDocInfo->getPhpDocNode()->children[] = $this->createJoinColumnTagNode();
        }
    }

    private function hasClassPropertyName(Class_ $node, string $uuidPropertyName): bool
    {
        foreach ($node->stmts as $stmt) {
            if (! $stmt instanceof Property) {
                continue;
            }

            if (! $this->isName($stmt, $uuidPropertyName)) {
                continue;
            }

            return true;
        }

        return false;
    }

    private function createJoinTableTagNode(Property $property): PhpDocTagNode
    {
        $joinTableName = $this->createManyToManyUuidTableName($property);

        $joinTableTagValueNode = new JoinTableTagValueNode($joinTableName, null, [
            new JoinColumnTagValueNode(null, 'uuid'),
        ], [new JoinColumnTagValueNode(null, 'uuid')]);

        return new AttributeAwarePhpDocTagNode(JoinTableTagValueNode::SHORT_NAME, $joinTableTagValueNode);
    }

    private function createJoinColumnTagNode(): PhpDocTagNode
    {
        $joinColumnTagValueNode = new JoinColumnTagValueNode(null, 'uuid', null, false);

        return new AttributeAwarePhpDocTagNode(JoinColumnTagValueNode::SHORT_NAME, $joinColumnTagValueNode);
    }
}
