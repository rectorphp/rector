<?php

declare(strict_types=1);

namespace Rector\DoctrineCodeQuality\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\DoctrineCodeQuality\NodeAnalyzer\ColumnDatetimePropertyAnalyzer;
use Rector\DoctrineCodeQuality\NodeFactory\ValueAssignFactory;
use Rector\DoctrineCodeQuality\NodeManipulator\ColumnDatetimePropertyManipulator;
use Rector\DoctrineCodeQuality\NodeManipulator\ConstructorManipulator;
use Rector\NodeCollector\NodeFinder\ClassLikeParsedNodesFinder;

/**
 * @sponsor Thanks https://www.luzanky.cz/ for sponsoring this rule
 *
 * @see https://stackoverflow.com/a/7698687/1348344
 *
 * @see \Rector\DoctrineCodeQuality\Tests\Rector\Property\MoveCurrentDateTimeDefaultInEntityToConstructorRector\MoveCurrentDateTimeDefaultInEntityToConstructorRectorTest
 */
final class MoveCurrentDateTimeDefaultInEntityToConstructorRector extends AbstractRector
{
    /**
     * @var ColumnDatetimePropertyAnalyzer
     */
    private $columnDatetimePropertyAnalyzer;

    /**
     * @var ConstructorManipulator
     */
    private $constructorManipulator;

    /**
     * @var ValueAssignFactory
     */
    private $valueAssignFactory;

    /**
     * @var ColumnDatetimePropertyManipulator
     */
    private $columnDatetimePropertyManipulator;

    public function __construct(
        ClassLikeParsedNodesFinder $classLikeParsedNodesFinder,
        ColumnDatetimePropertyAnalyzer $columnDatetimePropertyAnalyzer,
        ConstructorManipulator $constructorManipulator,
        ValueAssignFactory $valueAssignFactory,
        ColumnDatetimePropertyManipulator $columnDatetimePropertyManipulator
    ) {
        $this->classLikeParsedNodesFinder = $classLikeParsedNodesFinder;
        $this->columnDatetimePropertyAnalyzer = $columnDatetimePropertyAnalyzer;
        $this->constructorManipulator = $constructorManipulator;
        $this->valueAssignFactory = $valueAssignFactory;
        $this->columnDatetimePropertyManipulator = $columnDatetimePropertyManipulator;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Move default value for entity property to constructor, the safest place', [
            new CodeSample(
                <<<'PHP'
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class User
{
    /**
     * @var DateTimeInterface
     *
     * @ORM\Column(type="datetime", nullable=false, options={"default"="now()"})
     */
    private $when = 'now()';
}
PHP

                ,
                <<<'PHP'
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class User
{
    /**
     * @var DateTimeInterface
     *
     * @ORM\Column(type="datetime", nullable=false)
     */
    private $when;

    public function __construct()
    {
        $this->when = new \DateTime();
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
        return [Class_::class, Property::class];
    }

    /**
     * @param Class_|Property $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node instanceof Property) {
            return $this->refactorProperty($node);
        }

        if ($node instanceof Class_) {
            return $this->refactorClass($node);
        }

        return null;
    }

    private function refactorProperty(Property $property): ?Property
    {
        if (! $this->isObjectType($property, 'DateTimeInterface')) {
            return null;
        }

        $columnTagValueNode = $this->columnDatetimePropertyAnalyzer->matchDateTimeColumnTagValueNodeInProperty(
            $property
        );
        if ($columnTagValueNode === null) {
            return null;
        }

        $this->columnDatetimePropertyManipulator->removeDefaultOption($columnTagValueNode);

        // 2. remove default value
        $onlyProperty = $property->props[0];
        $onlyProperty->default = null;

        return $property;
    }

    private function refactorClass(Class_ $class): ?Class_
    {
        foreach ($class->getProperties() as $property) {
            if (! $this->isObjectType($property, 'DateTimeInterface')) {
                return null;
            }

            $columnTagValueNode = $this->columnDatetimePropertyAnalyzer->matchDateTimeColumnTagValueNodeInProperty(
                $property
            );

            if ($columnTagValueNode === null) {
                continue;
            }

            /** @var string $propertyName */
            $propertyName = $this->getName($property);
            $assign = $this->valueAssignFactory->createDefaultDateTimeAssign($propertyName);
            $this->constructorManipulator->addStmtToConstructor($class, $assign);
        }

        return $class;
    }
}
