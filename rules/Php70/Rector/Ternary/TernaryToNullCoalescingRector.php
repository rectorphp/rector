<?php

declare (strict_types=1);
namespace Rector\Php70\Rector\Ternary;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\BinaryOp\Coalesce;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Isset_;
use PhpParser\Node\Expr\Ternary;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\Application\File;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Php70\Rector\Ternary\TernaryToNullCoalescingRector\TernaryToNullCoalescingRectorTest
 */
final class TernaryToNullCoalescingRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     */
    private ValueResolver $valueResolver;
    public function __construct(ValueResolver $valueResolver)
    {
        $this->valueResolver = $valueResolver;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Changes unneeded null check to ?? operator', [new CodeSample('$value === null ? 10 : $value;', '$value ?? 10;'), new CodeSample('isset($value) ? $value : 10;', '$value ?? 10;'), new CodeSample('is_null($value) ? 10 : $value;', '$value ?? 10;')]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Ternary::class];
    }
    /**
     * @param Ternary $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node->cond instanceof Isset_) {
            return $this->processTernaryWithIsset($node, $node->cond);
        }
        if ($node->cond instanceof FuncCall && $this->isName($node->cond, 'is_null')) {
            return $this->processTernaryWithIsNull($node, $node->cond, \false);
        }
        if ($node->cond instanceof BooleanNot && $node->cond->expr instanceof FuncCall && $this->isName($node->cond->expr, 'is_null')) {
            return $this->processTernaryWithIsNull($node, $node->cond->expr, \true);
        }
        if ($node->cond instanceof Identical) {
            $checkedNode = $node->else;
            $fallbackNode = $node->if;
        } elseif ($node->cond instanceof NotIdentical) {
            $checkedNode = $node->if;
            $fallbackNode = $node->else;
        } else {
            // not a match
            return null;
        }
        if (!$checkedNode instanceof Expr) {
            return null;
        }
        if (!$fallbackNode instanceof Expr) {
            return null;
        }
        $ternaryCompareNode = $node->cond;
        if ($this->isNullMatch($ternaryCompareNode->left, $ternaryCompareNode->right, $checkedNode)) {
            return $this->createCoalesce($checkedNode, $fallbackNode);
        }
        if ($this->isNullMatch($ternaryCompareNode->right, $ternaryCompareNode->left, $checkedNode)) {
            return $this->createCoalesce($checkedNode, $fallbackNode);
        }
        return null;
    }
    public function provideMinPhpVersion(): int
    {
        return PhpVersionFeature::NULL_COALESCE;
    }
    private function processTernaryWithIsNull(Ternary $ternary, FuncCall $isNullFuncCall, bool $isNegated): ?Coalesce
    {
        if (count($isNullFuncCall->args) !== 1) {
            return null;
        }
        $firstArg = $isNullFuncCall->args[0];
        if (!$firstArg instanceof Arg) {
            return null;
        }
        $checkedExpr = $firstArg->value;
        if ($isNegated) {
            if (!$ternary->if instanceof Expr) {
                return null;
            }
            if (!$this->nodeComparator->areNodesEqual($ternary->if, $checkedExpr)) {
                return null;
            }
            $this->preserveWrappedFallback($ternary->else);
            return $this->createCoalesce($ternary->if, $ternary->else);
        }
        if (!$this->nodeComparator->areNodesEqual($ternary->else, $checkedExpr)) {
            return null;
        }
        if (!$ternary->if instanceof Expr) {
            return null;
        }
        $this->preserveWrappedFallback($ternary->if);
        return $this->createCoalesce($ternary->else, $ternary->if);
    }
    private function processTernaryWithIsset(Ternary $ternary, Isset_ $isset): ?Coalesce
    {
        if (!$ternary->if instanceof Expr) {
            return null;
        }
        if ($isset->vars === []) {
            return null;
        }
        // none or multiple isset values cannot be handled here
        if (count($isset->vars) > 1) {
            return null;
        }
        if (!$this->nodeComparator->areNodesEqual($ternary->if, $isset->vars[0])) {
            return null;
        }
        if (($ternary->else instanceof Ternary || $ternary->else instanceof BinaryOp) && $this->isTernaryParenthesized($this->getFile(), $ternary->cond, $ternary)) {
            $ternary->else->setAttribute(AttributeKey::WRAPPED_IN_PARENTHESES, \true);
        }
        return $this->createCoalesce($ternary->if, $ternary->else);
    }
    private function isTernaryParenthesized(File $file, Expr $expr, Ternary $ternary): bool
    {
        $oldTokens = $file->getOldTokens();
        $endTokenPost = $ternary->getEndTokenPos();
        if (isset($oldTokens[$endTokenPost]) && (string) $oldTokens[$endTokenPost] === ')') {
            $startTokenPos = $ternary->else->getStartTokenPos();
            $previousEndTokenPost = $expr->getEndTokenPos();
            while ($startTokenPos > $previousEndTokenPost) {
                --$startTokenPos;
                if (!isset($oldTokens[$startTokenPos])) {
                    return \false;
                }
                // handle space before open parentheses
                if (trim((string) $oldTokens[$startTokenPos]) === '') {
                    continue;
                }
                return (string) $oldTokens[$startTokenPos] === '(';
            }
            return \false;
        }
        return \false;
    }
    private function isNullMatch(Expr $possibleNullExpr, Expr $firstNode, Expr $secondNode): bool
    {
        if (!$this->valueResolver->isNull($possibleNullExpr)) {
            return \false;
        }
        return $this->nodeComparator->areNodesEqual($firstNode, $secondNode);
    }
    private function createCoalesce(Expr $checkedExpr, Expr $fallbackExpr): Coalesce
    {
        if ($this->isExprParenthesized($this->getFile(), $checkedExpr)) {
            $checkedExpr->setAttribute(AttributeKey::WRAPPED_IN_PARENTHESES, \true);
        }
        return new Coalesce($checkedExpr, $fallbackExpr);
    }
    private function preserveWrappedFallback(Expr $expr): void
    {
        if (!$expr instanceof BinaryOp && !$expr instanceof Ternary) {
            return;
        }
        if (!$this->isExprParenthesized($this->getFile(), $expr)) {
            return;
        }
        $expr->setAttribute(AttributeKey::WRAPPED_IN_PARENTHESES, \true);
    }
    private function isExprParenthesized(File $file, Expr $expr): bool
    {
        $oldTokens = $file->getOldTokens();
        $startTokenPos = $expr->getStartTokenPos();
        $endTokenPos = $expr->getEndTokenPos();
        while (isset($oldTokens[$startTokenPos - 1]) && trim((string) $oldTokens[$startTokenPos - 1]) === '') {
            --$startTokenPos;
        }
        while (isset($oldTokens[$endTokenPos + 1]) && trim((string) $oldTokens[$endTokenPos + 1]) === '') {
            ++$endTokenPos;
        }
        return isset($oldTokens[$startTokenPos - 1], $oldTokens[$endTokenPos + 1]) && (string) $oldTokens[$startTokenPos - 1] === '(' && (string) $oldTokens[$endTokenPos + 1] === ')';
    }
}
