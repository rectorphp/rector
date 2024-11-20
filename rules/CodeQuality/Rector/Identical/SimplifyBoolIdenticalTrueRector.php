<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\Identical;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\BooleanNot;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\Identical\SimplifyBoolIdenticalTrueRector\SimplifyBoolIdenticalTrueRectorTest
 */
final class SimplifyBoolIdenticalTrueRector extends AbstractRector
{
    /**
     * @readonly
     */
    private ValueResolver $valueResolver;
    public function __construct(ValueResolver $valueResolver)
    {
        $this->valueResolver = $valueResolver;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Simplify bool value compare to true or false', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run(bool $value, string $items)
    {
         $match = in_array($value, $items, TRUE) === TRUE;

         $match = in_array($value, $items, TRUE) !== FALSE;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run(bool $value, string $items)
    {
         $match = in_array($value, $items, TRUE);

         $match = in_array($value, $items, TRUE);
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
        return [Identical::class, NotIdentical::class];
    }
    /**
     * @param Identical|NotIdentical $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($this->isBooleanButNotTrueAndFalse($node->left)) {
            return $this->processBoolTypeToNotBool($node, $node->left, $node->right);
        }
        if ($this->isBooleanButNotTrueAndFalse($node->right)) {
            return $this->processBoolTypeToNotBool($node, $node->right, $node->left);
        }
        return null;
    }
    private function processBoolTypeToNotBool(Node $node, Expr $leftExpr, Expr $rightExpr) : ?Expr
    {
        if ($node instanceof Identical) {
            return $this->refactorIdentical($leftExpr, $rightExpr);
        }
        if ($node instanceof NotIdentical) {
            return $this->refactorNotIdentical($leftExpr, $rightExpr);
        }
        return null;
    }
    private function refactorIdentical(Expr $leftExpr, Expr $rightExpr) : ?Expr
    {
        if ($this->valueResolver->isTrue($rightExpr)) {
            return $leftExpr;
        }
        // prevent double negation !!
        if (!$this->valueResolver->isFalse($rightExpr)) {
            return null;
        }
        if (!$leftExpr instanceof BooleanNot) {
            return null;
        }
        return $leftExpr->expr;
    }
    private function refactorNotIdentical(Expr $leftExpr, Expr $rightExpr) : ?Expr
    {
        if ($this->valueResolver->isFalse($rightExpr)) {
            return $leftExpr;
        }
        if ($this->valueResolver->isTrue($rightExpr)) {
            return new BooleanNot($leftExpr);
        }
        return null;
    }
    private function isBooleanButNotTrueAndFalse(Expr $expr) : bool
    {
        if ($this->valueResolver->isTrueOrFalse($expr)) {
            return \false;
        }
        return $this->nodeTypeResolver->getNativeType($expr)->isBoolean()->yes();
    }
}
