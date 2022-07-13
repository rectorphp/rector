<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Rector\Closure;

use PhpParser\Node;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\Variable;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodingStyle\Rector\Closure\StaticClosureRector\StaticClosureRectorTest
 */
final class StaticClosureRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Changes Closure to be static when possible', [new CodeSample(<<<'CODE_SAMPLE'
function () {
    if (rand(0, 1)) {
        return 1;
    }

    return 2;
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
static function () {
    if (rand(0, 1)) {
        return 1;
    }

    return 2;
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Closure::class];
    }
    /**
     * @param Closure $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }
        $node->static = \true;
        return $node;
    }
    private function shouldSkip(Closure $closure) : bool
    {
        if ($closure->static) {
            return \true;
        }
        return (bool) $this->betterNodeFinder->findFirst($closure->stmts, static function (Node $subNode) : bool {
            return $subNode instanceof Variable && $subNode->name === 'this';
        });
    }
}
