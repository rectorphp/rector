<?php

declare (strict_types=1);
namespace Rector\Doctrine\CodeQuality\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\UnaryMinus;
use PhpParser\Node\Expr\UnaryPlus;
use PhpParser\Node\PropertyItem;
use PhpParser\Node\Scalar\Int_;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Property;
use Rector\BetterPhpDocParser\PhpDoc\ArrayItemNode;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\BetterPhpDocParser\PhpDoc\StringNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Doctrine\Enum\MappingClass;
use Rector\Exception\NotImplementedYetException;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Doctrine\Tests\CodeQuality\Rector\Property\CorrectDefaultTypesOnEntityPropertyRector\CorrectDefaultTypesOnEntityPropertyRectorTest
 */
final class CorrectDefaultTypesOnEntityPropertyRector extends AbstractRector
{
    /**
     * @readonly
     */
    private PhpDocInfoFactory $phpDocInfoFactory;
    /**
     * @readonly
     */
    private ValueResolver $valueResolver;
    public function __construct(PhpDocInfoFactory $phpDocInfoFactory, ValueResolver $valueResolver)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->valueResolver = $valueResolver;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change default value types to match Doctrine annotation type', [new CodeSample(<<<'CODE_SAMPLE'
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class User
{
    /**
     * @ORM\Column(name="is_old", type="boolean")
     */
    private $isOld = '0';
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
     * @ORM\Column(name="is_old", type="boolean")
     */
    private $isOld = false;
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Property::class];
    }
    /**
     * @param Property $node
     */
    public function refactor(Node $node) : ?Node
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        $doctrineAnnotationTagValueNode = $phpDocInfo->getByAnnotationClass(MappingClass::COLUMN);
        if (!$doctrineAnnotationTagValueNode instanceof DoctrineAnnotationTagValueNode) {
            return null;
        }
        $onlyProperty = $node->props[0];
        $defaultValue = $onlyProperty->default;
        if (!$defaultValue instanceof Expr) {
            return null;
        }
        $typeArrayItemNode = $doctrineAnnotationTagValueNode->getValue('type');
        if (!$typeArrayItemNode instanceof ArrayItemNode) {
            return null;
        }
        $typeValue = $typeArrayItemNode->value;
        if ($typeValue instanceof StringNode) {
            $typeValue = $typeValue->value;
        }
        if (!\is_string($typeValue)) {
            return null;
        }
        if (\in_array($typeValue, ['bool', 'boolean'], \true)) {
            return $this->refactorToBoolType($onlyProperty, $node);
        }
        if (\in_array($typeValue, ['int', 'integer', 'bigint', 'smallint'], \true)) {
            return $this->refactorToIntType($onlyProperty, $node);
        }
        return null;
    }
    private function refactorToBoolType(PropertyItem $propertyItem, Property $property) : ?Property
    {
        if (!$propertyItem->default instanceof Expr) {
            return null;
        }
        $defaultExpr = $propertyItem->default;
        if ($defaultExpr instanceof String_) {
            $propertyItem->default = (bool) $defaultExpr->value ? $this->nodeFactory->createTrue() : $this->nodeFactory->createFalse();
            return $property;
        }
        if ($defaultExpr instanceof ConstFetch || $defaultExpr instanceof ClassConstFetch) {
            // already ok
            return null;
        }
        throw new NotImplementedYetException();
    }
    private function refactorToIntType(PropertyItem $propertyItem, Property $property) : ?Property
    {
        if (!$propertyItem->default instanceof Expr) {
            return null;
        }
        $defaultExpr = $propertyItem->default;
        if ($defaultExpr instanceof String_) {
            $propertyItem->default = new Int_((int) $defaultExpr->value);
            return $property;
        }
        if ($defaultExpr instanceof Int_) {
            // already correct
            return null;
        }
        // default value on nullable property
        if ($this->valueResolver->isNull($defaultExpr)) {
            return null;
        }
        if ($defaultExpr instanceof ClassConstFetch || $defaultExpr instanceof ConstFetch) {
            return null;
        }
        if ($defaultExpr instanceof UnaryMinus || $defaultExpr instanceof UnaryPlus) {
            return null;
        }
        throw new NotImplementedYetException();
    }
}
