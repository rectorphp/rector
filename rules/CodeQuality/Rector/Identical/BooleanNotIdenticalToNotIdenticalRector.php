<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\CodeQuality\Rector\Identical;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr\BinaryOp\Identical;
use RectorPrefix20220606\PhpParser\Node\Expr\BinaryOp\NotIdentical;
use RectorPrefix20220606\PhpParser\Node\Expr\BooleanNot;
use RectorPrefix20220606\PHPStan\Type\BooleanType;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://3v4l.org/GoEPq
 * @see \Rector\Tests\CodeQuality\Rector\Identical\BooleanNotIdenticalToNotIdenticalRector\BooleanNotIdenticalToNotIdenticalRectorTest
 */
final class BooleanNotIdenticalToNotIdenticalRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Negated identical boolean compare to not identical compare (does not apply to non-bool values)', [new CodeSample(<<<'CODE_SAMPLE'
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
        return [Identical::class, BooleanNot::class];
    }
    /**
     * @param Identical|BooleanNot $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($node instanceof Identical) {
            return $this->processIdentical($node);
        }
        if ($node->expr instanceof Identical) {
            $identical = $node->expr;
            $leftType = $this->getType($identical->left);
            if (!$leftType instanceof BooleanType) {
                return null;
            }
            $rightType = $this->getType($identical->right);
            if (!$rightType instanceof BooleanType) {
                return null;
            }
            return new NotIdentical($identical->left, $identical->right);
        }
        return null;
    }
    private function processIdentical(Identical $identical) : ?NotIdentical
    {
        $leftType = $this->getType($identical->left);
        if (!$leftType instanceof BooleanType) {
            return null;
        }
        $rightType = $this->getType($identical->right);
        if (!$rightType instanceof BooleanType) {
            return null;
        }
        if ($identical->left instanceof BooleanNot) {
            return new NotIdentical($identical->left->expr, $identical->right);
        }
        return null;
    }
}
