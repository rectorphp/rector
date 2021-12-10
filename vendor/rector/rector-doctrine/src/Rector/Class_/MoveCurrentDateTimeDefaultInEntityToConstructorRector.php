<?php

declare (strict_types=1);
namespace Rector\Doctrine\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDoc\DoctrineAnnotation\CurlyListNode;
use Rector\Core\Rector\AbstractRector;
use Rector\Doctrine\NodeAnalyzer\ConstructorAssignPropertyAnalyzer;
use Rector\Doctrine\NodeFactory\ValueAssignFactory;
use Rector\Doctrine\NodeManipulator\ConstructorManipulator;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://stackoverflow.com/a/7698687/1348344
 *
 * @see \Rector\Doctrine\Tests\Rector\Class_\MoveCurrentDateTimeDefaultInEntityToConstructorRector\MoveCurrentDateTimeDefaultInEntityToConstructorRectorTest
 */
final class MoveCurrentDateTimeDefaultInEntityToConstructorRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var \Rector\Doctrine\NodeManipulator\ConstructorManipulator
     */
    private $constructorManipulator;
    /**
     * @var \Rector\Doctrine\NodeFactory\ValueAssignFactory
     */
    private $valueAssignFactory;
    /**
     * @var \Rector\Doctrine\NodeAnalyzer\ConstructorAssignPropertyAnalyzer
     */
    private $constructorAssignPropertyAnalyzer;
    public function __construct(\Rector\Doctrine\NodeManipulator\ConstructorManipulator $constructorManipulator, \Rector\Doctrine\NodeFactory\ValueAssignFactory $valueAssignFactory, \Rector\Doctrine\NodeAnalyzer\ConstructorAssignPropertyAnalyzer $constructorAssignPropertyAnalyzer)
    {
        $this->constructorManipulator = $constructorManipulator;
        $this->valueAssignFactory = $valueAssignFactory;
        $this->constructorAssignPropertyAnalyzer = $constructorAssignPropertyAnalyzer;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Move default value for entity property to constructor, the safest place', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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
CODE_SAMPLE
, <<<'CODE_SAMPLE'
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
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        foreach ($node->getProperties() as $property) {
            $this->refactorProperty($property, $node);
        }
        return $node;
    }
    private function refactorProperty(\PhpParser\Node\Stmt\Property $property, \PhpParser\Node\Stmt\Class_ $class) : ?\PhpParser\Node\Stmt\Property
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);
        $doctrineAnnotationTagValueNode = $phpDocInfo->getByAnnotationClass('Doctrine\\ORM\\Mapping\\Column');
        if (!$doctrineAnnotationTagValueNode instanceof \Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode) {
            return null;
        }
        $type = $doctrineAnnotationTagValueNode->getValueWithoutQuotes('type');
        if ($type !== 'datetime') {
            return null;
        }
        $constructorAssign = $this->constructorAssignPropertyAnalyzer->resolveConstructorAssign($property);
        // 0. already has default
        if ($constructorAssign !== null) {
            return null;
        }
        // 1. remove default options from database level
        $options = $doctrineAnnotationTagValueNode->getValue('options');
        if ($options instanceof \Rector\BetterPhpDocParser\ValueObject\PhpDoc\DoctrineAnnotation\CurlyListNode) {
            $options->removeValue('default');
            // if empty, remove it completely
            if ($options->getValues() === []) {
                $doctrineAnnotationTagValueNode->removeValue('options');
            }
        }
        $phpDocInfo->markAsChanged();
        $this->refactorClass($class, $property);
        // 3. remove default from property
        $onlyProperty = $property->props[0];
        $onlyProperty->default = null;
        return $property;
    }
    private function refactorClass(\PhpParser\Node\Stmt\Class_ $class, \PhpParser\Node\Stmt\Property $property) : void
    {
        /** @var string $propertyName */
        $propertyName = $this->getName($property);
        $onlyProperty = $property->props[0];
        $defaultExpr = $onlyProperty->default;
        if (!$defaultExpr instanceof \PhpParser\Node\Expr) {
            return;
        }
        if ($this->valueResolver->isNull($defaultExpr)) {
            return;
        }
        $expression = $this->valueAssignFactory->createDefaultDateTimeWithValueAssign($propertyName, $defaultExpr);
        $this->constructorManipulator->addStmtToConstructor($class, $expression);
    }
}
