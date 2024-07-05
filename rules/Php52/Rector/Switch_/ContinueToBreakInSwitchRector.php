<?php

declare (strict_types=1);
namespace Rector\Php52\Rector\Switch_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Break_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Continue_;
use PhpParser\Node\Stmt\Do_;
use PhpParser\Node\Stmt\For_;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Switch_;
use PhpParser\Node\Stmt\While_;
use PHPStan\Type\Constant\ConstantIntegerType;
use PHPStan\Type\ConstantType;
use Rector\Contract\PhpParser\Node\StmtsAwareInterface;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Php52\Rector\Switch_\ContinueToBreakInSwitchRector\ContinueToBreakInSwitchRectorTest
 */
final class ContinueToBreakInSwitchRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\Value\ValueResolver
     */
    private $valueResolver;
    /**
     * @var bool
     */
    private $hasChanged = \false;
    public function __construct(ValueResolver $valueResolver)
    {
        $this->valueResolver = $valueResolver;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::CONTINUE_TO_BREAK;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Use break instead of continue in switch statements', [new CodeSample(<<<'CODE_SAMPLE'
function some_run($value)
{
    switch ($value) {
        case 1:
            echo 'Hi';
            continue;
        case 2:
            echo 'Hello';
            break;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
function some_run($value)
{
    switch ($value) {
        case 1:
            echo 'Hi';
            break;
        case 2:
            echo 'Hello';
            break;
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
        return [Switch_::class];
    }
    /**
     * @param Switch_ $node
     */
    public function refactor(Node $node) : ?Switch_
    {
        $this->hasChanged = \false;
        foreach ($node->cases as $case) {
            $this->processContinueStatement($case);
        }
        if (!$this->hasChanged) {
            return null;
        }
        return $node;
    }
    /**
     * @param \PhpParser\Node\Stmt|\Rector\Contract\PhpParser\Node\StmtsAwareInterface $stmt
     */
    private function processContinueStatement($stmt) : void
    {
        if ($stmt instanceof Class_ || $stmt instanceof Function_ || $stmt instanceof While_ || $stmt instanceof Do_ || $stmt instanceof Foreach_ || $stmt instanceof For_) {
            return;
        }
        if (!$stmt instanceof StmtsAwareInterface) {
            return;
        }
        if ($stmt->stmts === null) {
            return;
        }
        foreach ($stmt->stmts as $key => $stmtStmt) {
            if ($stmtStmt instanceof StmtsAwareInterface) {
                $this->processContinueStatement($stmtStmt);
                continue;
            }
            if (!$stmtStmt instanceof Continue_) {
                continue;
            }
            if (!$stmtStmt->num instanceof Expr) {
                $this->hasChanged = \true;
                $stmt->stmts[$key] = new Break_();
                continue;
            }
            if ($stmtStmt->num instanceof LNumber) {
                $continueNumber = $this->valueResolver->getValue($stmtStmt->num);
                if ($continueNumber <= 1) {
                    $this->hasChanged = \true;
                    $stmt->stmts[$key] = new Break_();
                    continue;
                }
            } elseif ($stmtStmt->num instanceof Variable) {
                $continue = $this->processVariableNum($stmtStmt, $stmtStmt->num);
                if ($continue instanceof Continue_) {
                    continue;
                }
                $this->hasChanged = \true;
                $stmt->stmts[$key] = new Break_();
            }
        }
    }
    /**
     * @return \PhpParser\Node\Stmt\Continue_|\PhpParser\Node\Stmt\Break_
     */
    private function processVariableNum(Continue_ $continue, Variable $numVariable)
    {
        $staticType = $this->getType($numVariable);
        if (!$staticType instanceof ConstantType) {
            return $continue;
        }
        if (!$staticType instanceof ConstantIntegerType) {
            return $continue;
        }
        if ($staticType->getValue() > 1) {
            return $continue;
        }
        return new Break_();
    }
}
