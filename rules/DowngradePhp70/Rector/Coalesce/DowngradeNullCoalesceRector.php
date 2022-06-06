<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\DowngradePhp70\Rector\Coalesce;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr\BinaryOp\Coalesce;
use RectorPrefix20220606\PhpParser\Node\Expr\BinaryOp\NotIdentical;
use RectorPrefix20220606\PhpParser\Node\Expr\Isset_;
use RectorPrefix20220606\PhpParser\Node\Expr\Ternary;
use RectorPrefix20220606\Rector\Core\NodeAnalyzer\CoalesceAnalyzer;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/isset_ternary
 *
 * @see \Rector\Tests\DowngradePhp70\Rector\Coalesce\DowngradeNullCoalesceRector\DowngradeNullCoalesceRectorTest
 */
final class DowngradeNullCoalesceRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Core\NodeAnalyzer\CoalesceAnalyzer
     */
    private $coalesceAnalyzer;
    public function __construct(CoalesceAnalyzer $coalesceAnalyzer)
    {
        $this->coalesceAnalyzer = $coalesceAnalyzer;
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Coalesce::class];
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change null coalesce to isset ternary check', [new CodeSample(<<<'CODE_SAMPLE'
$username = $_GET['user'] ?? 'nobody';
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$username = isset($_GET['user']) ? $_GET['user'] : 'nobody';
CODE_SAMPLE
)]);
    }
    /**
     * @param Coalesce $node
     */
    public function refactor(Node $node) : Ternary
    {
        $if = $node->left;
        $else = $node->right;
        if ($this->coalesceAnalyzer->hasIssetableLeft($node)) {
            $cond = new Isset_([$if]);
        } else {
            $cond = new NotIdentical($if, $this->nodeFactory->createNull());
        }
        return new Ternary($cond, $if, $else);
    }
}
