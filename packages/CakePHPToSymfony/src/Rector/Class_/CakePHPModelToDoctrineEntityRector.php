<?php

declare(strict_types=1);

namespace Rector\CakePHPToSymfony\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Rector\CakePHPToSymfony\NodeFactory\RelationPropertyFactory;
use Rector\Doctrine\PhpDocParser\Ast\PhpDoc\PhpDocTagNodeFactory;
use Rector\PhpParser\Node\Manipulator\ClassManipulator;
use Rector\PHPStan\Type\AliasedObjectType;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

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
     * @var ClassManipulator
     */
    private $classManipulator;

    /**
     * @var PhpDocTagNodeFactory
     */
    private $phpDocTagNodeFactory;

    /**
     * @var RelationPropertyFactory
     */
    private $relationPropertyFactory;

    public function __construct(
        ClassManipulator $classManipulator,
        PhpDocTagNodeFactory $phpDocTagNodeFactory,
        RelationPropertyFactory $relationPropertyFactory
    ) {
        $this->classManipulator = $classManipulator;
        $this->phpDocTagNodeFactory = $phpDocTagNodeFactory;
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
        $belongsToProperty = $this->classManipulator->getProperty($node, 'belongsTo');
        if ($belongsToProperty !== null) {
            $manyToOneProperties = $this->relationPropertyFactory->createManyToOneProperties($belongsToProperty);
            $relationProperties = array_merge($relationProperties, $manyToOneProperties);

            $this->removeNode($belongsToProperty);
        }

        $hasAndBelongsToMany = $this->classManipulator->getProperty($node, 'hasAndBelongsToMany');
        if ($hasAndBelongsToMany !== null) {
            $manyToManyProperties = $this->relationPropertyFactory->createManyToManyProperties($hasAndBelongsToMany);
            $relationProperties = array_merge($relationProperties, $manyToManyProperties);

            $this->removeNode($hasAndBelongsToMany);
        }

        if ($relationProperties !== []) {
            $node->stmts = array_merge($relationProperties, (array) $node->stmts);
        }

        return $node;
    }

    private function addEntityTagToEntityClass(Class_ $node): void
    {
        $entityTag = $this->phpDocTagNodeFactory->createEntityTag();
        $this->docBlockManipulator->addTag($node, $entityTag);

        $objectType = new AliasedObjectType('ORM', 'Doctrine\Mapping\Annotation');
        $this->addUseType($objectType, $node);
    }
}
