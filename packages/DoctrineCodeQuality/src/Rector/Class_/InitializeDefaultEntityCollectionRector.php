<?php

declare(strict_types=1);

namespace Rector\DoctrineCodeQuality\Rector\Class_;

use Doctrine\Common\Collections\ArrayCollection;
use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Expression;
use Rector\BetterPhpDocParser\Contract\Doctrine\ToManyTagNodeInterface;
use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Class_\EntityTagValueNode;
use Rector\PhpParser\Node\Manipulator\ClassManipulator;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see https://www.doctrine-project.org/projects/doctrine-orm/en/2.6/reference/best-practices.html#initialize-collections-in-the-constructor
 *
 * @see \Rector\DoctrineCodeQuality\Tests\Rector\Class_\InitializeDefaultEntityCollectionRector\InitializeDefaultEntityCollectionRectorTest
 */
final class InitializeDefaultEntityCollectionRector extends AbstractRector
{
    /**
     * @var ClassManipulator
     */
    private $classManipulator;

    public function __construct(ClassManipulator $classManipulator)
    {
        $this->classManipulator = $classManipulator;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Initialize collection property in Entity constructor', [
            new CodeSample(
                <<<'PHP'
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class SomeClass
{
    /**
     * @ORM\OneToMany(targetEntity="MarketingEvent")
     */
    private $marketingEvents = [];
}
PHP
                ,
                <<<'PHP'
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class SomeClass
{
    /**
     * @ORM\OneToMany(targetEntity="MarketingEvent")
     */
    private $marketingEvents = [];

    public function __construct()
    {
        $this->marketingEvents = new ArrayCollection();
    }
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
        $classPhpDocInfo = $this->getPhpDocInfo($node);
        if ($classPhpDocInfo === null) {
            return null;
        }

        if (! $classPhpDocInfo->getByType(EntityTagValueNode::class)) {
            return null;
        }

        $toManyPropertyNames = $this->resolveToManyPropertyNames($node);
        if ($toManyPropertyNames === []) {
            return null;
        }

        $assigns = $this->createAssignsOfArrayCollectionsForPropertyNames($toManyPropertyNames);

        $this->classManipulator->addStmtsToClassMethodIfNotThereYet($node, '__construct', $assigns);

        return $node;
    }

    /**
     * @return string[]
     */
    private function resolveToManyPropertyNames(Class_ $class): array
    {
        $collectionPropertyNames = [];

        foreach ($class->getProperties() as $property) {
            if (count($property->props) !== 1) {
                continue;
            }

            $propertyPhpDocInfo = $this->getPhpDocInfo($property);
            if ($propertyPhpDocInfo === null) {
                continue;
            }

            if (! $propertyPhpDocInfo->getByType(ToManyTagNodeInterface::class)) {
                continue;
            }

            /** @var string $propertyName */
            $propertyName = $this->getName($property);

            $collectionPropertyNames[] = $propertyName;
        }

        return $collectionPropertyNames;
    }

    /**
     * @param string[] $propertyNames
     * @return Expression[]
     */
    private function createAssignsOfArrayCollectionsForPropertyNames(array $propertyNames): array
    {
        $assigns = [];
        foreach ($propertyNames as $propertyName) {
            $assigns[] = $this->createPropertyArrayCollectionAssign($propertyName);
        }

        return $assigns;
    }

    private function createPropertyArrayCollectionAssign(string $toManyPropertyName): Expression
    {
        $propertyFetch = $this->createPropertyFetch('this', $toManyPropertyName);
        $newCollection = new New_(new FullyQualified(ArrayCollection::class));

        $assign = new Assign($propertyFetch, $newCollection);

        return new Expression($assign);
    }
}
