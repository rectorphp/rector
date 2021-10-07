<?php

declare (strict_types=1);
namespace Rector\Php52\Rector\Switch_;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Stmt\Break_;
use PhpParser\Node\Stmt\Continue_;
use PhpParser\Node\Stmt\Switch_;
use PHPStan\Type\Constant\ConstantIntegerType;
use PHPStan\Type\ConstantType;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://stackoverflow.com/a/12349889/1348344
 * @see \Rector\Tests\Php52\Rector\Switch_\ContinueToBreakInSwitchRector\ContinueToBreakInSwitchRectorTest
 */
final class ContinueToBreakInSwitchRector extends \Rector\Core\Rector\AbstractRector implements \Rector\VersionBonding\Contract\MinPhpVersionInterface
{
    public function provideMinPhpVersion() : int
    {
        return \Rector\Core\ValueObject\PhpVersionFeature::CONTINUE_TO_BREAK;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Use break instead of continue in switch statements', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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
        return [\PhpParser\Node\Stmt\Switch_::class];
    }
    /**
     * @param Switch_ $node
     */
    public function refactor(\PhpParser\Node $node) : \PhpParser\Node\Stmt\Switch_
    {
        foreach ($node->cases as $case) {
            foreach ($case->stmts as $key => $caseStmt) {
                if (!$caseStmt instanceof \PhpParser\Node\Stmt\Continue_) {
                    continue;
                }
                $case->stmts[$key] = $this->processContinueStatement($caseStmt);
            }
        }
        return $node;
    }
    /**
     * @return \PhpParser\Node\Stmt\Break_|\PhpParser\Node\Stmt\Continue_
     */
    private function processContinueStatement(\PhpParser\Node\Stmt\Continue_ $continue)
    {
        if ($continue->num === null) {
            return new \PhpParser\Node\Stmt\Break_();
        }
        if ($continue->num instanceof \PhpParser\Node\Scalar\LNumber) {
            $continueNumber = $this->valueResolver->getValue($continue->num);
            if ($continueNumber <= 1) {
                return new \PhpParser\Node\Stmt\Break_();
            }
        } elseif ($continue->num instanceof \PhpParser\Node\Expr\Variable) {
            return $this->processVariableNum($continue, $continue->num);
        }
        return $continue;
    }
    /**
     * @return \PhpParser\Node\Stmt\Continue_|\PhpParser\Node\Stmt\Break_
     */
    private function processVariableNum(\PhpParser\Node\Stmt\Continue_ $continue, \PhpParser\Node\Expr\Variable $numVariable)
    {
        $staticType = $this->getType($numVariable);
        if (!$staticType instanceof \PHPStan\Type\ConstantType) {
            return $continue;
        }
        if (!$staticType instanceof \PHPStan\Type\Constant\ConstantIntegerType) {
            return $continue;
        }
        if ($staticType->getValue() > 1) {
            return $continue;
        }
        return new \PhpParser\Node\Stmt\Break_();
    }
}
