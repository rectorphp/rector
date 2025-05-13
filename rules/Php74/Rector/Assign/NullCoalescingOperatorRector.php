<?php

declare (strict_types=1);
namespace Rector\Php74\Rector\Assign;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\AssignOp\Coalesce as AssignCoalesce;
use PhpParser\Node\Expr\BinaryOp\Coalesce;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Php74\Rector\Assign\NullCoalescingOperatorRector\NullCoalescingOperatorRectorTest
 */
final class NullCoalescingOperatorRector extends AbstractRector implements MinPhpVersionInterface
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Use null coalescing operator `??=`', [new CodeSample(<<<'CODE_SAMPLE'
$array = [];
$array['user_id'] = $array['user_id'] ?? 'value';
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$array = [];
$array['user_id'] ??= 'value';
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Assign::class];
    }
    /**
     * @param Assign $node
     */
    public function refactor(Node $node) : ?AssignCoalesce
    {
        if (!$node->expr instanceof Coalesce) {
            return null;
        }
        if (!$this->nodeComparator->areNodesEqual($node->var, $node->expr->left)) {
            return null;
        }
        return new AssignCoalesce($node->var, $node->expr->right);
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::NULL_COALESCE_ASSIGN;
    }
}
