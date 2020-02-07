<?php

declare(strict_types=1);

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
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see https://stackoverflow.com/a/12349889/1348344
 * @see \Rector\Php52\Tests\Rector\Switch_\ContinueToBreakInSwitchRector\ContinueToBreakInSwitchRectorTest
 */
final class ContinueToBreakInSwitchRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Use break instead of continue in switch statements', [
            new CodeSample(
                <<<'PHP'
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
PHP
                ,
                <<<'PHP'
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
PHP
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
                if ($caseStmt instanceof Continue_) {
                    $case->stmts[$key] = $this->processContinueStatement($caseStmt);
                }
            }
        }

        return $node;
    }

    private function processContinueStatement(Continue_ $node): Node
    {
        if ($node->num === null) {
            return new Break_();
        } elseif ($node->num instanceof LNumber) {
            if ($this->getValue($node->num) <= 1) {
                return new Break_();
            }
        } elseif ($node->num instanceof Variable) {
            return $this->processVariableNum($node, $node->num);
        }

        return $node;
    }

    private function processVariableNum(Continue_ $node, Variable $numVariable): Node
    {
        $staticType = $this->getStaticType($numVariable);

        if ($staticType instanceof ConstantType && $staticType instanceof ConstantIntegerType && $staticType->getValue() <= 1) {
            return new Break_();
        }

        return $node;
    }
}
