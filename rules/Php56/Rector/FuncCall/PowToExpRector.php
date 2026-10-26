<?php

declare (strict_types=1);
namespace Rector\Php56\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\Pow;
use PhpParser\Node\Expr\FuncCall;
use Rector\NodeAnalyzer\PowOperandAnalyzer;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Rector\AbstractRector;
use Rector\RuleDoc\CodeSample\CodeSample;
use Rector\RuleDoc\RuleDefinition;
use Rector\ValueObject\PhpVersion;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
/**
 * @see \Rector\Tests\Php56\Rector\FuncCall\PowToExpRector\PowToExpRectorTest
 */
final class PowToExpRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     */
    private PowOperandAnalyzer $powOperandAnalyzer;
    public function __construct(PowOperandAnalyzer $powOperandAnalyzer)
    {
        $this->powOperandAnalyzer = $powOperandAnalyzer;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Changes `pow(val, val2)` to `**` (exp) parameter', [new CodeSample('pow(1, 2);', '1**2;')]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [FuncCall::class];
    }
    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (!$this->isName($node, 'pow')) {
            return null;
        }
        if ($node->isFirstClassCallable()) {
            return null;
        }
        return $this->createPow($node);
    }
    public function provideMinPhpVersion(): int
    {
        return PhpVersion::PHP_56;
    }
    private function createPow(FuncCall $funcCall): ?Pow
    {
        $args = $funcCall->getArgs();
        if (count($args) < 2) {
            return null;
        }
        // convert nested pow() calls first, so pow(pow(...), ...) resolves in a single pass
        $firstExpr = $this->createPowFromNested($args[0]->value);
        $secondExpr = $this->createPowFromNested($args[1]->value);
        // ** binds tighter than most operators, so operands with lower precedence must be
        // wrapped in parentheses to keep the original semantics, e.g. pow(~3, 4) => (~3) ** 4
        if ($this->powOperandAnalyzer->isLowerPrecedenceAsLeftOperand($firstExpr)) {
            $firstExpr->setAttribute(AttributeKey::WRAPPED_IN_PARENTHESES, \true);
        }
        if ($this->powOperandAnalyzer->isLowerPrecedenceAsRightOperand($secondExpr)) {
            $secondExpr->setAttribute(AttributeKey::WRAPPED_IN_PARENTHESES, \true);
        }
        return new Pow($firstExpr, $secondExpr);
    }
    private function createPowFromNested(Expr $expr): Expr
    {
        if ($expr instanceof FuncCall && !$expr->isFirstClassCallable() && $this->isName($expr, 'pow')) {
            $pow = $this->createPow($expr);
            if ($pow instanceof Pow) {
                return $pow;
            }
        }
        return $expr;
    }
}
