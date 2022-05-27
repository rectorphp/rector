<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\Identical;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\BooleanNot;
use PHPStan\Type\BooleanType;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://3v4l.org/GoEPq
 * @see \Rector\Tests\CodeQuality\Rector\Identical\BooleanNotIdenticalToNotIdenticalRector\BooleanNotIdenticalToNotIdenticalRectorTest
 */
final class BooleanNotIdenticalToNotIdenticalRector extends \Rector\Core\Rector\AbstractRector
{
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Negated identical boolean compare to not identical compare (does not apply to non-bool values)', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $a = true;
        $b = false;

        var_dump(! $a === $b); // true
        var_dump(! ($a === $b)); // true
        var_dump($a !== $b); // true
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $a = true;
        $b = false;

        var_dump($a !== $b); // true
        var_dump($a !== $b); // true
        var_dump($a !== $b); // true
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
        return [\PhpParser\Node\Expr\BinaryOp\Identical::class, \PhpParser\Node\Expr\BooleanNot::class];
    }
    /**
     * @param Identical|BooleanNot $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($node instanceof \PhpParser\Node\Expr\BinaryOp\Identical) {
            return $this->processIdentical($node);
        }
        if ($node->expr instanceof \PhpParser\Node\Expr\BinaryOp\Identical) {
            $identical = $node->expr;
            $leftType = $this->getType($identical->left);
            if (!$leftType instanceof \PHPStan\Type\BooleanType) {
                return null;
            }
            $rightType = $this->getType($identical->right);
            if (!$rightType instanceof \PHPStan\Type\BooleanType) {
                return null;
            }
            return new \PhpParser\Node\Expr\BinaryOp\NotIdentical($identical->left, $identical->right);
        }
        return null;
    }
    private function processIdentical(\PhpParser\Node\Expr\BinaryOp\Identical $identical) : ?\PhpParser\Node\Expr\BinaryOp\NotIdentical
    {
        $leftType = $this->getType($identical->left);
        if (!$leftType instanceof \PHPStan\Type\BooleanType) {
            return null;
        }
        $rightType = $this->getType($identical->right);
        if (!$rightType instanceof \PHPStan\Type\BooleanType) {
            return null;
        }
        if ($identical->left instanceof \PhpParser\Node\Expr\BooleanNot) {
            return new \PhpParser\Node\Expr\BinaryOp\NotIdentical($identical->left->expr, $identical->right);
        }
        return null;
    }
}
