<?php

declare(strict_types=1);

namespace Rector\CakePHPToSymfony\Rector\Class_;

use PhpParser\Builder\Property;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Rector\BetterPhpDocParser\Attributes\Ast\PhpDoc\SpacelessPhpDocTagNode;
use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Property_\JoinColumnTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Property_\ManyToOneTagValueNode;
use Rector\Doctrine\PhpDocParser\Ast\PhpDoc\PhpDocTagNodeFactory;
use Rector\Exception\ShouldNotHappenException;
use Rector\PhpParser\Node\Manipulator\ClassManipulator;
use Rector\PHPStan\Type\AliasedObjectType;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
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

    public function __construct(ClassManipulator $classManipulator, PhpDocTagNodeFactory $phpDocTagNodeFactory)
    {
        $this->classManipulator = $classManipulator;
        $this->phpDocTagNodeFactory = $phpDocTagNodeFactory;
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

        // extract relations
        $belongToProperty = $this->classManipulator->getProperty($node, 'belongsTo');
        if ($belongToProperty !== null) {
            $onlyPropertyDefault = $belongToProperty->props[0]->default;
            if ($onlyPropertyDefault === null) {
                throw new ShouldNotHappenException();
            }

            $belongsToValue = $this->getValue($onlyPropertyDefault);

            foreach ($belongsToValue as $propertyName => $manyToOneConfiguration) {
                $propertyName = lcfirst($propertyName);
                $propertyBuilder = new Property($propertyName);
                $propertyBuilder->makePrivate();

                $className = $manyToOneConfiguration['className'];

                // add @ORM\ManyToOne
                $manyToOneTagValueNode = new ManyToOneTagValueNode($className, null, null, null, null, $className);
                $propertyNode = $propertyBuilder->getNode();
                $spacelessPhpDocTagNode = new SpacelessPhpDocTagNode(
                    ManyToOneTagValueNode::SHORT_NAME,
                    $manyToOneTagValueNode
                );
                $this->docBlockManipulator->addTag($propertyNode, $spacelessPhpDocTagNode);

                $joinColumnTagValueNode = new JoinColumnTagValueNode($manyToOneConfiguration['foreignKey'], null);
                $spacelessPhpDocTagNode = new SpacelessPhpDocTagNode(
                    JoinColumnTagValueNode::SHORT_NAME,
                    $joinColumnTagValueNode
                );
                $this->docBlockManipulator->addTag($propertyNode, $spacelessPhpDocTagNode);

                $node->stmts[] = $propertyNode;
            }

            $this->removeNode($belongToProperty);
        }

        $entityTag = $this->phpDocTagNodeFactory->createEntityTag();
        $this->docBlockManipulator->addTag($node, $entityTag);

        $objectType = new AliasedObjectType('ORM', 'Doctrine\Mapping\Annotation');
        $this->addUseType($objectType, $node);

        return $node;
    }
}
