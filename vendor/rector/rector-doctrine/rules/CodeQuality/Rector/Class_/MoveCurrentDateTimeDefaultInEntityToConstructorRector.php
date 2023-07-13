<?php

declare (strict_types=1);
namespace Rector\Doctrine\CodeQuality\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprTrueNode;
use Rector\BetterPhpDocParser\PhpDoc\ArrayItemNode;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\BetterPhpDocParser\PhpDoc\StringNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDoc\DoctrineAnnotation\CurlyListNode;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\MethodName;
use Rector\Doctrine\NodeAnalyzer\ConstructorAssignPropertyAnalyzer;
use Rector\Doctrine\NodeFactory\ValueAssignFactory;
use Rector\Doctrine\NodeManipulator\ConstructorManipulator;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://stackoverflow.com/a/7698687/1348344
 *
 * @see \Rector\Doctrine\Tests\CodeQuality\Rector\Class_\MoveCurrentDateTimeDefaultInEntityToConstructorRector\MoveCurrentDateTimeDefaultInEntityToConstructorRectorTest
 */
final class MoveCurrentDateTimeDefaultInEntityToConstructorRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Doctrine\NodeManipulator\ConstructorManipulator
     */
    private $constructorManipulator;
    /**
     * @readonly
     * @var \Rector\Doctrine\NodeFactory\ValueAssignFactory
     */
    private $valueAssignFactory;
    /**
     * @readonly
     * @var \Rector\Doctrine\NodeAnalyzer\ConstructorAssignPropertyAnalyzer
     */
    private $constructorAssignPropertyAnalyzer;
    /**
     * @var bool
     */
    private $hasChanged = \false;
    public function __construct(ConstructorManipulator $constructorManipulator, ValueAssignFactory $valueAssignFactory, ConstructorAssignPropertyAnalyzer $constructorAssignPropertyAnalyzer)
    {
        $this->constructorManipulator = $constructorManipulator;
        $this->valueAssignFactory = $valueAssignFactory;
        $this->constructorAssignPropertyAnalyzer = $constructorAssignPropertyAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Move default value for entity property to constructor, the safest place', [new CodeSample(<<<'CODE_SAMPLE'
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
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        $this->hasChanged = \false;
        foreach ($node->getProperties() as $property) {
            $this->refactorProperty($property, $node);
        }
        if ($this->hasChanged) {
            return $node;
        }
        return null;
    }
    private function refactorProperty(Property $property, Class_ $class) : void
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);
        $doctrineAnnotationTagValueNode = $phpDocInfo->getByAnnotationClass('Doctrine\\ORM\\Mapping\\Column');
        if (!$doctrineAnnotationTagValueNode instanceof DoctrineAnnotationTagValueNode) {
            return;
        }
        $typeArrayItemNode = $doctrineAnnotationTagValueNode->getValue('type');
        if (!$typeArrayItemNode instanceof ArrayItemNode) {
            return;
        }
        $typeValue = $typeArrayItemNode->value;
        if ($typeValue instanceof StringNode) {
            $typeValue = $typeValue->value;
        }
        if ($typeValue !== 'datetime') {
            return;
        }
        $constructorAssign = $this->constructorAssignPropertyAnalyzer->resolveConstructorAssign($class, $property);
        // skip nullable
        $nullableArrayItemNode = $doctrineAnnotationTagValueNode->getValue('nullable');
        if ($nullableArrayItemNode instanceof ArrayItemNode && $nullableArrayItemNode->value instanceof ConstExprTrueNode) {
            return;
        }
        // 0. already has default
        if ($constructorAssign instanceof Assign) {
            return;
        }
        // 1. remove default options from database level
        $optionsArrayItemNode = $doctrineAnnotationTagValueNode->getValue('options');
        if ($optionsArrayItemNode instanceof ArrayItemNode) {
            if (!$optionsArrayItemNode->value instanceof CurlyListNode) {
                return;
            }
            $optionsArrayItemNode->value->removeValue('default');
            // if empty, remove it completely
            if ($optionsArrayItemNode->value->getValues() === []) {
                $doctrineAnnotationTagValueNode->removeValue('options');
            }
            $this->hasChanged = \true;
        }
        if ($this->hasChanged) {
            $phpDocInfo->markAsChanged();
        }
        $this->refactorClassWithRemovalDefault($class, $property);
    }
    private function refactorClassWithRemovalDefault(Class_ $class, Property $property) : void
    {
        $this->refactorClass($class, $property);
        $classMethod = $class->getMethod(MethodName::CONSTRUCT);
        if (!$classMethod instanceof ClassMethod && $property->type instanceof Node) {
            return;
        }
        // 3. remove default from property
        $onlyProperty = $property->props[0];
        $onlyProperty->default = null;
        $this->hasChanged = \true;
    }
    private function refactorClass(Class_ $class, Property $property) : void
    {
        /** @var string $propertyName */
        $propertyName = $this->getName($property);
        $onlyProperty = $property->props[0];
        $defaultExpr = $onlyProperty->default;
        if (!$defaultExpr instanceof Expr) {
            return;
        }
        if ($this->valueResolver->isNull($defaultExpr)) {
            return;
        }
        $expression = $this->valueAssignFactory->createDefaultDateTimeWithValueAssign($propertyName, $defaultExpr);
        $this->constructorManipulator->addStmtToConstructor($class, $expression);
    }
}
