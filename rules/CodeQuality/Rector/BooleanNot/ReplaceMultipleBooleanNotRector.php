<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\BooleanNot;

use PhpParser\Node;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\Cast\Bool_;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\BooleanNot\ReplaceMultipleBooleanNotRector\ReplaceMultipleBooleanNotRectorTest
 */
final class ReplaceMultipleBooleanNotRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Replace the Double not operator (!!) by type-casting to boolean', [new CodeSample(<<<'CODE_SAMPLE'
$bool = !!$var;
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$bool = (bool) $var;
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [BooleanNot::class];
    }
    /**
     * @param BooleanNot $node
     */
    public function refactor(Node $node) : ?Node
    {
        $depth = 0;
        $expr = $node->expr;
        while ($expr instanceof BooleanNot) {
            ++$depth;
            $expr = $expr->expr;
        }
        if ($depth === 0) {
            return null;
        }
        if ($depth % 2 === 0) {
            $node->expr = $expr;
            return $node;
        }
        return new Bool_($expr);
    }
}
