<?php

declare (strict_types=1);
namespace Rector\Restoration\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\ComplexType;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\PropertyProperty;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Restoration\Rector\Property\MakeTypedPropertyNullableIfCheckedRector\MakeTypedPropertyNullableIfCheckedRectorTest
 */
final class MakeTypedPropertyNullableIfCheckedRector extends \Rector\Core\Rector\AbstractRector
{
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Make typed property nullable if checked', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    private AnotherClass $anotherClass;

    public function run()
    {
        if ($this->anotherClass === null) {
            $this->anotherClass = new AnotherClass;
        }
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    private ?AnotherClass $anotherClass = null;

    public function run()
    {
        if ($this->anotherClass === null) {
            $this->anotherClass = new AnotherClass;
        }
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
        return [\PhpParser\Node\Stmt\Property::class];
    }
    /**
     * @param Property $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($this->shouldSkipProperty($node)) {
            return null;
        }
        /** @var PropertyProperty $onlyProperty */
        $onlyProperty = $node->props[0];
        $isPropretyNullChecked = $this->isPropertyNullChecked($onlyProperty);
        if (!$isPropretyNullChecked) {
            return null;
        }
        if ($node->type instanceof \PhpParser\Node\ComplexType) {
            return null;
        }
        $currentPropertyType = $node->type;
        if ($currentPropertyType === null) {
            return null;
        }
        $node->type = new \PhpParser\Node\NullableType($currentPropertyType);
        $onlyProperty->default = $this->nodeFactory->createNull();
        return $node;
    }
    private function shouldSkipProperty(\PhpParser\Node\Stmt\Property $property) : bool
    {
        if (\count($property->props) !== 1) {
            return \true;
        }
        if ($property->type === null) {
            return \true;
        }
        return $property->type instanceof \PhpParser\Node\NullableType;
    }
    private function isPropertyNullChecked(\PhpParser\Node\Stmt\PropertyProperty $onlyPropertyProperty) : bool
    {
        $classLike = $onlyPropertyProperty->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CLASS_NODE);
        if (!$classLike instanceof \PhpParser\Node\Stmt\Class_) {
            return \false;
        }
        if ($this->isIdenticalOrNotIdenticalToNull($classLike, $onlyPropertyProperty)) {
            return \true;
        }
        return $this->isBooleanNot($classLike, $onlyPropertyProperty);
    }
    private function isIdenticalOrNotIdenticalToNull(\PhpParser\Node\Stmt\Class_ $class, \PhpParser\Node\Stmt\PropertyProperty $onlyPropertyProperty) : bool
    {
        $isIdenticalOrNotIdenticalToNull = \false;
        $this->traverseNodesWithCallable($class->stmts, function (\PhpParser\Node $node) use($onlyPropertyProperty, &$isIdenticalOrNotIdenticalToNull) {
            $matchedPropertyFetchName = $this->matchPropertyFetchNameComparedToNull($node);
            if ($matchedPropertyFetchName === null) {
                return null;
            }
            if (!$this->isName($onlyPropertyProperty, $matchedPropertyFetchName)) {
                return null;
            }
            $isIdenticalOrNotIdenticalToNull = \true;
        });
        return $isIdenticalOrNotIdenticalToNull;
    }
    private function isBooleanNot(\PhpParser\Node\Stmt\Class_ $class, \PhpParser\Node\Stmt\PropertyProperty $onlyPropertyProperty) : bool
    {
        $isBooleanNot = \false;
        $this->traverseNodesWithCallable($class->stmts, function (\PhpParser\Node $node) use($onlyPropertyProperty, &$isBooleanNot) {
            if (!$node instanceof \PhpParser\Node\Expr\BooleanNot) {
                return null;
            }
            if (!$node->expr instanceof \PhpParser\Node\Expr\PropertyFetch) {
                return null;
            }
            if (!$this->isName($node->expr->var, 'this')) {
                return null;
            }
            if (!$this->nodeNameResolver->areNamesEqual($onlyPropertyProperty, $node->expr->name)) {
                return null;
            }
            $isBooleanNot = \true;
        });
        return $isBooleanNot;
    }
    /**
     * Matches:
     * $this-><someProprety> === null
     * null === $this-><someProprety>
     */
    private function matchPropertyFetchNameComparedToNull(\PhpParser\Node $node) : ?string
    {
        if (!$node instanceof \PhpParser\Node\Expr\BinaryOp\Identical && !$node instanceof \PhpParser\Node\Expr\BinaryOp\NotIdentical) {
            return null;
        }
        if ($node->left instanceof \PhpParser\Node\Expr\PropertyFetch && $this->valueResolver->isNull($node->right)) {
            $propertyFetch = $node->left;
        } elseif ($node->right instanceof \PhpParser\Node\Expr\PropertyFetch && $this->valueResolver->isNull($node->left)) {
            $propertyFetch = $node->right;
        } else {
            return null;
        }
        if (!$this->isName($propertyFetch->var, 'this')) {
            return null;
        }
        return $this->getName($propertyFetch->name);
    }
}
