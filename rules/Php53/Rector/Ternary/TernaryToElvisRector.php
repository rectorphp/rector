<?php

declare (strict_types=1);
namespace Rector\Php53\Rector\Ternary;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Ternary;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Php53\Rector\Ternary\TernaryToElvisRector\TernaryToElvisRectorTest
 */
final class TernaryToElvisRector extends AbstractRector implements MinPhpVersionInterface
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Use `?:` instead of `?`, where useful', [new CodeSample(<<<'CODE_SAMPLE'
function elvis()
{
    $value = $a ? $a : false;
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
function elvis()
{
    $value = $a ?: false;
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Ternary::class];
    }
    /**
     * @param Ternary $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->nodeComparator->areNodesEqual($node->cond, $node->if)) {
            return null;
        }
        $node->setAttribute(AttributeKey::ORIGINAL_NODE, null);
        /** @var Expr $nodeIf */
        $nodeIf = $node->if;
        if ($node->else instanceof Ternary && $this->isParenthesized($nodeIf, $node->else)) {
            $node->else->setAttribute(AttributeKey::WRAPPED_IN_PARENTHESES, \true);
        }
        $node->if = null;
        return $node;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::ELVIS_OPERATOR;
    }
    private function isParenthesized(Expr $ifExpr, Expr $elseExpr) : bool
    {
        $tokens = $this->file->getOldTokens();
        $ifExprTokenEnd = $ifExpr->getEndTokenPos();
        $elseExprTokenStart = $elseExpr->getStartTokenPos();
        if ($ifExprTokenEnd < 0 || $elseExprTokenStart < 0 || $elseExprTokenStart <= $ifExprTokenEnd) {
            return \false;
        }
        while (isset($tokens[$ifExprTokenEnd])) {
            ++$ifExprTokenEnd;
            if ($elseExprTokenStart === $ifExprTokenEnd) {
                break;
            }
            if ((string) $tokens[$ifExprTokenEnd] === '(') {
                return \true;
            }
        }
        return \false;
    }
}
