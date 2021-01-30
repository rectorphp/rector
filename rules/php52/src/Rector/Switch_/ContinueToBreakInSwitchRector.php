<?php

declare(strict_types=1);

namespace Rector\Php52\Rector\Switch_;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Break_;
use PhpParser\Node\Stmt\Continue_;
use PhpParser\Node\Stmt\Switch_;
use PHPStan\Type\Constant\ConstantIntegerType;
use PHPStan\Type\ConstantType;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see https://stackoverflow.com/a/12349889/1348344
 * @see \Rector\Php52\Tests\Rector\Switch_\ContinueToBreakInSwitchRector\ContinueToBreakInSwitchRectorTest
 */
final class ContinueToBreakInSwitchRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Use break instead of continue in switch statements',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
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
                    ,
                    <<<'CODE_SAMPLE'
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
                ),

            ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Switch_::class];
    }

    /**
     * @param Switch_ $node
     */
    public function refactor(Node $node): ?Node
    {
        foreach ($node->cases as $case) {
            foreach ($case->stmts as $key => $caseStmt) {
                if (! $caseStmt instanceof Continue_) {
                    continue;
                }

                $case->stmts[$key] = $this->processContinueStatement($caseStmt);
            }
        }

        return $node;
    }

    private function processContinueStatement(Continue_ $continue): Stmt
    {
        if ($continue->num === null) {
            return new Break_();
        }

        if ($continue->num instanceof LNumber) {
            $continueNumber = $this->valueResolver->getValue($continue->num);
            if ($continueNumber <= 1) {
                return new Break_();
            }
        } elseif ($continue->num instanceof Variable) {
            return $this->processVariableNum($continue, $continue->num);
        }

        return $continue;
    }

    private function processVariableNum(Continue_ $continue, Variable $numVariable): Stmt
    {
        $staticType = $this->getStaticType($numVariable);

        if ($staticType instanceof ConstantType && $staticType instanceof ConstantIntegerType && $staticType->getValue() <= 1) {
            return new Break_();
        }

        return $continue;
    }
}
