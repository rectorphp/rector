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
final class ReplaceMultipleBooleanNotRector extends \Rector\Core\Rector\AbstractRector
{
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Replace the Double not operator (!!) by type-casting to boolean', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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
        return [\PhpParser\Node\Expr\BooleanNot::class];
    }
    /**
     * @param BooleanNot $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $depth = 0;
        $expr = $node->expr;
        while ($expr instanceof \PhpParser\Node\Expr\BooleanNot) {
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
        return new \PhpParser\Node\Expr\Cast\Bool_($expr);
    }
}
