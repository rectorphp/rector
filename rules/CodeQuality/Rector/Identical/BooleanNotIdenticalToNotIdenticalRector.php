<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\Identical;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\BooleanNot;
use Rector\Rector\AbstractRector;
use Rector\RuleDoc\CodeSample\CodeSample;
use Rector\RuleDoc\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\Identical\BooleanNotIdenticalToNotIdenticalRector\BooleanNotIdenticalToNotIdenticalRectorTest
 */
final class BooleanNotIdenticalToNotIdenticalRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
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
    public function getNodeTypes(): array
    {
        return [Identical::class, BooleanNot::class];
    }
    /**
     * @param Identical|BooleanNot $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node instanceof Identical) {
            return $this->processIdentical($node);
        }
        if ($node->expr instanceof Identical) {
            $identical = $node->expr;
            $leftType = $this->getType($identical->left);
            if (!$leftType->isBoolean()->yes()) {
                return null;
            }
            $rightType = $this->getType($identical->right);
            if (!$rightType->isBoolean()->yes()) {
                return null;
            }
            return new NotIdentical($identical->left, $identical->right);
        }
        return null;
    }
    private function processIdentical(Identical $identical): ?NotIdentical
    {
        // cheap structural guard first, so getType() runs only on the "!$a === $b" shape
        if (!$identical->left instanceof BooleanNot) {
            return null;
        }
        if (!$this->getType($identical->left)->isBoolean()->yes()) {
            return null;
        }
        if (!$this->getType($identical->right)->isBoolean()->yes()) {
            return null;
        }
        return new NotIdentical($identical->left->expr, $identical->right);
    }
}
