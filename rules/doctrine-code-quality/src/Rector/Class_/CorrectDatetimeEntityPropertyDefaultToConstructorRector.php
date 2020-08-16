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

/**
 * @see https://stackoverflow.com/a/7698687/1348344
 *
 * @todo possible merge with
 * @see MoveCurrentDateTimeDefaultInEntityToConstructorRector
 *
 * @see \Rector\DoctrineCodeQuality\Tests\Rector\Property\CorrectDatetimeEntityPropertyDefaultToConstructorRector\CorrectDatetimeEntityPropertyDefaultToConstructorRectorTest
 */
final class CorrectDatetimeEntityPropertyDefaultToConstructorRector extends AbstractRector
{
    /**
     * @var ColumnDatetimePropertyAnalyzer
     */
    private $columnDatetimePropertyAnalyzer;

    /**
     * @var ValueAssignFactory
     */
    private $valueAssignFactory;

    /**
     * @var ConstructorManipulator
     */
    private $constructorManipulator;

    /**
     * @var ColumnDatetimePropertyManipulator
     */
    private $columnDatetimePropertyManipulator;

    public function __construct(
        ColumnDatetimePropertyAnalyzer $columnDatetimePropertyAnalyzer,
        ValueAssignFactory $valueAssignFactory,
        ConstructorManipulator $constructorManipulator,
        ColumnDatetimePropertyManipulator $columnDatetimePropertyManipulator
    ) {
        $this->columnDatetimePropertyAnalyzer = $columnDatetimePropertyAnalyzer;
        $this->valueAssignFactory = $valueAssignFactory;
        $this->constructorManipulator = $constructorManipulator;
        $this->columnDatetimePropertyManipulator = $columnDatetimePropertyManipulator;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change default value in string on datetime property to entity constructor', [
            new CodeSample(
                <<<'PHP'
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class User
{
    /**
     * @ORM\Column(name="log_cas", type="datetime", nullable=false, options={"default"="1900-01-01 00=00=00"})
     */
    private $when = '1900-01-01 00:00:00';
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
     * @ORM\Column(name="log_cas", type="datetime", nullable=false)
     */
    private $when;

    public function __construct()
    {
        $this->when = new DateTime('1900-01-01 00:00:00');
    }
}
PHP
            ),
        ]);
    }

    /**
     * @return array<int, string>
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
        foreach ($node->getProperties() as $property) {
            $this->refactorProperty($property, $node);
        }

        return $node;
    }

    private function refactorProperty(Property $property, Class_ $class): void
    {
        if (! $this->isDoctrineProperty($property)) {
            return;
        }

        // nothing to change → skip
        $onlyProperty = $property->props[0];
        if ($onlyProperty->default === null) {
            return;
        }

        $columnTagValueNode = $this->columnDatetimePropertyAnalyzer->matchDateTimeColumnTagValueNodeInProperty(
            $property
        );
        if ($columnTagValueNode === null) {
            return;
        }

        $defaultValue = $onlyProperty->default;
        $onlyProperty->default = null;

        $this->columnDatetimePropertyManipulator->removeDefaultOption($columnTagValueNode);

        /** @var string $propertyName */
        $propertyName = $this->getName($onlyProperty);
        $expression = $this->valueAssignFactory->createDefaultDateTimeWithValueAssign($propertyName, $defaultValue);
        $this->constructorManipulator->addStmtToConstructor($class, $expression);
    }
}
