<?php

declare(strict_types=1);

namespace Rector\Doctrine\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\PropertyProperty;
use PhpParser\Node\VarLikeIdentifier;
use Rector\BetterPhpDocParser\Contract\Doctrine\DoctrineRelationTagValueNodeInterface;
use Rector\BetterPhpDocParser\Contract\Doctrine\ToManyTagNodeInterface;
use Rector\BetterPhpDocParser\Contract\Doctrine\ToOneTagNodeInterface;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Property_\JoinColumnTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Property_\OneToOneTagValueNode;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Doctrine\Collector\UuidMigrationDataCollector;
use Rector\Doctrine\PhpDocParser\Ast\PhpDoc\PhpDocTagNodeFactory;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @sponsor Thanks https://spaceflow.io/ for sponsoring this rule - visit them on https://github.com/SpaceFlow-app
 *
 * @see \Rector\Doctrine\Tests\Rector\Class_\AddUuidMirrorForRelationPropertyRector\AddUuidMirrorForRelationPropertyRectorTest
 */
final class AddUuidMirrorForRelationPropertyRector extends AbstractRector
{
    /**
     * @var PhpDocTagNodeFactory
     */
    private $phpDocTagNodeFactory;

    /**
     * @var UuidMigrationDataCollector
     */
    private $uuidMigrationDataCollector;

    public function __construct(
        PhpDocTagNodeFactory $phpDocTagNodeFactory,
        UuidMigrationDataCollector $uuidMigrationDataCollector
    ) {
        $this->phpDocTagNodeFactory = $phpDocTagNodeFactory;
        $this->uuidMigrationDataCollector = $uuidMigrationDataCollector;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Adds $uuid property to entities, that already have $id with integer type.' .
            'Require for step-by-step migration from int to uuid.',
            [
                new CodeSample(
                <<<'CODE_SAMPLE'
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class SomeEntity
{
    /**
     * @ORM\ManyToOne(targetEntity="AnotherEntity", cascade={"persist", "merge"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $amenity;
}

/**
 * @ORM\Entity
 */
class AnotherEntity
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    private $uuid;
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class SomeEntity
{
    /**
     * @ORM\ManyToOne(targetEntity="AnotherEntity", cascade={"persist", "merge"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $amenity;

    /**
     * @ORM\ManyToOne(targetEntity="AnotherEntity", cascade={"persist", "merge"})
     * @ORM\JoinColumn(nullable=true, referencedColumnName="uuid")
     */
    private $amenityUuid;
}

/**
 * @ORM\Entity
 */
class AnotherEntity
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    private $uuid;
}
CODE_SAMPLE
    ), ]
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
        foreach ($node->getProperties() as $property) {
            if ($this->shouldSkipProperty($node, $property)) {
                continue;
            }

            $node->stmts[] = $this->createMirrorNullable($property);
        }

        return $node;
    }

    private function shouldSkipProperty(Class_ $class, Property $property): bool
    {
        // this relation already is or has uuid property
        if ($this->isName($property, '*Uuid')) {
            return true;
        }

        $uuidPropertyName = $this->getName($property) . 'Uuid';
        if ($this->hasClassPropertyName($class, $uuidPropertyName)) {
            return true;
        }

        $targetEntity = $this->getTargetEntity($property);
        if ($targetEntity === null) {
            return true;
        }

        if (! property_exists($targetEntity, 'uuid')) {
            return true;
        }

        /** @var PhpDocInfo|null $propertyPhpDocInfo */
        $propertyPhpDocInfo = $property->getAttribute(AttributeKey::PHP_DOC_INFO);
        if ($propertyPhpDocInfo === null) {
            return true;
        }

        $oneToOneTagValueNode = $propertyPhpDocInfo->getByType(OneToOneTagValueNode::class);
        // skip mappedBy oneToOne, as the column doesn't really exist
        return $oneToOneTagValueNode !== null && $oneToOneTagValueNode->getMappedBy();
    }

    /**
     * Creates duplicated property, that has "*uuidSuffix"
     * and nullable join column, so we cna complete them manually
     */
    private function createMirrorNullable(Property $property): Property
    {
        $oldPropertyName = $this->getName($property);

        $propertyWithUuid = clone $property;

        // this is needed to keep old property name
        $this->mirrorPhpDocInfoToUuid($propertyWithUuid);

        // name must be changed after the doc comment update, because the reflection annotation needed for update of doc comment
        // would miss non existing *Uuid property
        $uuidPropertyName = $oldPropertyName . 'Uuid';
        $newPropertyProperty = new PropertyProperty(new VarLikeIdentifier($uuidPropertyName));
        $propertyWithUuid->props = [$newPropertyProperty];

        $this->addNewPropertyToCollector($property, $oldPropertyName, $uuidPropertyName);

        return $propertyWithUuid;
    }

    private function hasClassPropertyName(Class_ $class, string $uuidPropertyName): bool
    {
        foreach ($class->getProperties() as $property) {
            if (! $this->isName($property, $uuidPropertyName)) {
                continue;
            }

            return true;
        }

        return false;
    }

    private function mirrorPhpDocInfoToUuid(Property $property): void
    {
        /** @var PhpDocInfo $propertyPhpDocInfo */
        $propertyPhpDocInfo = $property->getAttribute(AttributeKey::PHP_DOC_INFO);

        $newPropertyPhpDocInfo = clone $propertyPhpDocInfo;

        /** @var DoctrineRelationTagValueNodeInterface $doctrineRelationTagValueNode */
        $doctrineRelationTagValueNode = $this->getDoctrineRelationTagValueNode($property);

        if ($doctrineRelationTagValueNode instanceof ToManyTagNodeInterface) {
            $this->refactorToManyPropertyPhpDocInfo($newPropertyPhpDocInfo, $property);
        } elseif ($doctrineRelationTagValueNode instanceof ToOneTagNodeInterface) {
            $this->refactorToOnePropertyPhpDocInfo($newPropertyPhpDocInfo);
        }
    }

    private function addNewPropertyToCollector(
        Property $property,
        string $oldPropertyName,
        string $uuidPropertyName
    ): void {
        /** @var string $className */
        $className = $property->getAttribute(AttributeKey::CLASS_NAME);

        /** @var DoctrineRelationTagValueNodeInterface $doctrineRelationTagValueNode */
        $doctrineRelationTagValueNode = $this->getDoctrineRelationTagValueNode($property);

        $this->uuidMigrationDataCollector->addClassToManyRelationProperty(
            $className,
            $oldPropertyName,
            $uuidPropertyName,
            $doctrineRelationTagValueNode
        );
    }

    private function refactorToManyPropertyPhpDocInfo(PhpDocInfo $propertyPhpDocInfo, Property $property): void
    {
        $doctrineJoinColumnTagValueNode = $propertyPhpDocInfo->getByType(JoinColumnTagValueNode::class);
        if ($doctrineJoinColumnTagValueNode !== null) {
            // replace @ORM\JoinColumn with @ORM\JoinTable
            $propertyPhpDocInfo->removeTagValueNodeFromNode($doctrineJoinColumnTagValueNode);
        }

        $joinTableTagNode = $this->phpDocTagNodeFactory->createJoinTableTagNode($property);
        $propertyPhpDocInfo->addPhpDocTagNode($joinTableTagNode);
    }

    private function refactorToOnePropertyPhpDocInfo(PhpDocInfo $propertyPhpDocInfo): void
    {
        /** @var JoinColumnTagValueNode|null $joinColumnTagValueNode */
        $joinColumnTagValueNode = $propertyPhpDocInfo->getByType(JoinColumnTagValueNode::class);

        if ($joinColumnTagValueNode !== null) {
            // remove first
            $propertyPhpDocInfo->removeByType(JoinColumnTagValueNode::class);

            $mirrorJoinColumnTagValueNode = new JoinColumnTagValueNode([
                'referencedColumnName' => 'uuid',
                'unique' => $joinColumnTagValueNode->getUnique(),
                'nullable' => true,
            ]);
        } else {
            $mirrorJoinColumnTagValueNode = $this->phpDocTagNodeFactory->createJoinColumnTagNode(true);
        }

        $propertyPhpDocInfo->addTagValueNodeWithShortName($mirrorJoinColumnTagValueNode);
    }
}
