<?php

declare (strict_types=1);
namespace Rector\Doctrine\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\PropertyProperty;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\Core\Exception\NotImplementedYetException;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Doctrine\Tests\Rector\Property\CorrectDefaultTypesOnEntityPropertyRector\CorrectDefaultTypesOnEntityPropertyRectorTest
 */
final class CorrectDefaultTypesOnEntityPropertyRector extends \Rector\Core\Rector\AbstractRector
{
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Change default value types to match Doctrine annotation type', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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
        return [\PhpParser\Node\Stmt\Property::class];
    }
    /**
     * @param Property $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        $doctrineAnnotationTagValueNode = $phpDocInfo->getByAnnotationClass('Doctrine\\ORM\\Mapping\\Column');
        if (!$doctrineAnnotationTagValueNode instanceof \Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode) {
            return null;
        }
        $onlyProperty = $node->props[0];
        $defaultValue = $onlyProperty->default;
        if (!$defaultValue instanceof \PhpParser\Node\Expr) {
            return null;
        }
        $type = $doctrineAnnotationTagValueNode->getValueWithoutQuotes('type');
        if (\in_array($type, ['bool', 'boolean'], \true)) {
            return $this->refactorToBoolType($onlyProperty, $node);
        }
        if (\in_array($type, ['int', 'integer', 'bigint', 'smallint'], \true)) {
            return $this->refactorToIntType($onlyProperty, $node);
        }
        return null;
    }
    private function refactorToBoolType(\PhpParser\Node\Stmt\PropertyProperty $propertyProperty, \PhpParser\Node\Stmt\Property $property) : ?\PhpParser\Node\Stmt\Property
    {
        if ($propertyProperty->default === null) {
            return null;
        }
        $defaultExpr = $propertyProperty->default;
        if ($defaultExpr instanceof \PhpParser\Node\Scalar\String_) {
            $propertyProperty->default = \boolval($defaultExpr->value) ? $this->nodeFactory->createTrue() : $this->nodeFactory->createFalse();
            return $property;
        }
        if ($defaultExpr instanceof \PhpParser\Node\Expr\ConstFetch || $defaultExpr instanceof \PhpParser\Node\Expr\ClassConstFetch) {
            // already ok
            return null;
        }
        throw new \Rector\Core\Exception\NotImplementedYetException();
    }
    private function refactorToIntType(\PhpParser\Node\Stmt\PropertyProperty $propertyProperty, \PhpParser\Node\Stmt\Property $property) : ?\PhpParser\Node\Stmt\Property
    {
        if ($propertyProperty->default === null) {
            return null;
        }
        $defaultExpr = $propertyProperty->default;
        if ($defaultExpr instanceof \PhpParser\Node\Scalar\String_) {
            $propertyProperty->default = new \PhpParser\Node\Scalar\LNumber((int) $defaultExpr->value);
            return $property;
        }
        if ($defaultExpr instanceof \PhpParser\Node\Scalar\LNumber) {
            // already correct
            return null;
        }
        // default value on nullable property
        if ($this->valueResolver->isNull($defaultExpr)) {
            return null;
        }
        throw new \Rector\Core\Exception\NotImplementedYetException();
    }
}
