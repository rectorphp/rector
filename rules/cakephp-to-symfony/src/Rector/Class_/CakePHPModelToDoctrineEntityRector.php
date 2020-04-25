<?php

declare(strict_types=1);

namespace Rector\CakePHPToSymfony\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Class_\EntityTagValueNode;
use Rector\CakePHPToSymfony\NodeFactory\RelationPropertyFactory;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PHPStan\Type\AliasedObjectType;

/**
 * @see https://book.cakephp.org/2/en/models/associations-linking-models-together.html#relationship-types
 * hasOne => one to one
 * hasMany => one to many
 * belongsTo => many to one
 * hasAndBelongsToMany => many to many
 *
 * @see https://book.cakephp.org/2/en/models/associations-linking-models-together.html#
 * @see https://www.doctrine-project.org/projects/doctrine-orm/en/2.6/reference/association-mapping.html
 *
 * @see \Rector\CakePHPToSymfony\Tests\Rector\Class_\CakePHPModelToDoctrineEntityRector\CakePHPModelToDoctrineEntityRectorTest
 */
final class CakePHPModelToDoctrineEntityRector extends AbstractRector
{
    /**
     * @var RelationPropertyFactory
     */
    private $relationPropertyFactory;

    public function __construct(RelationPropertyFactory $relationPropertyFactory)
    {
        $this->relationPropertyFactory = $relationPropertyFactory;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Migrate CakePHP Model active record to Doctrine\ORM Entity and EntityRepository', [
            new CodeSample(
                <<<'PHP'
class Activity extends \AppModel
{
    public $belongsTo = [
        'ActivityType' => [
            'className' => 'ActivityType',
            'foreignKey' => 'activity_type_id',
            'dependent' => false,
        ],
    ];
}
PHP
,
                <<<'PHP'
use Doctrine\Mapping\Annotation as ORM;

/**
 * @ORM\Entity
 */
class Activity
{
    /**
     * @ORM\ManyToOne(targetEntity="ActivityType")
     * @ORM\JoinColumn(name="activity_type_id")
     */
    private $activityType;
}
PHP

            ),
        ]);
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
        if (! $this->isObjectType($node, 'AppModel')) {
            return null;
        }

        $node->extends = null;
        $this->addEntityTagToEntityClass($node);

        $relationProperties = [];

        // extract relations
        $belongsToProperty = $node->getProperty('belongsTo');
        if ($belongsToProperty !== null) {
            $manyToOneProperties = $this->relationPropertyFactory->createManyToOneProperties($belongsToProperty);
            $relationProperties = array_merge($relationProperties, $manyToOneProperties);

            $this->removeNode($belongsToProperty);
        }

        $hasAndBelongsToManyProperty = $node->getProperty('hasAndBelongsToMany');
        if ($hasAndBelongsToManyProperty !== null) {
            $newRelationProperties = $this->relationPropertyFactory->createManyToManyProperties(
                $hasAndBelongsToManyProperty
            );
            $relationProperties = array_merge($relationProperties, $newRelationProperties);

            $this->removeNode($hasAndBelongsToManyProperty);
        }

        $hasManyProperty = $node->getProperty('hasMany');
        if ($hasManyProperty !== null) {
            $newRelationProperties = $this->relationPropertyFactory->createOneToManyProperties($hasManyProperty);
            $relationProperties = array_merge($relationProperties, $newRelationProperties);

            $this->removeNode($hasManyProperty);
        }

        $hasOneProperty = $node->getProperty('hasOne');
        if ($hasOneProperty !== null) {
            $newRelationProperties = $this->relationPropertyFactory->createOneToOneProperties($hasOneProperty);
            $relationProperties = array_merge($relationProperties, $newRelationProperties);

            $this->removeNode($hasOneProperty);
        }

        if ($relationProperties !== []) {
            $node->stmts = array_merge($relationProperties, (array) $node->stmts);
        }

        return $node;
    }

    private function addEntityTagToEntityClass(Class_ $class): void
    {
        /** @var PhpDocInfo $phpDocInfo */
        $phpDocInfo = $class->getAttribute(AttributeKey::PHP_DOC_INFO);
        $phpDocInfo->addTagValueNodeWithShortName(new EntityTagValueNode());

        $objectType = new AliasedObjectType('ORM', 'Doctrine\Mapping\Annotation');
        $this->addUseType($objectType, $class);
    }
}
