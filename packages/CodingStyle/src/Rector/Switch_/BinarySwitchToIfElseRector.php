<?php declare(strict_types=1);

namespace Rector\CodingStyle\Rector\Switch_;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;
use PhpParser\Node\Expr\BinaryOp\Equal;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Break_;
use PhpParser\Node\Stmt\Case_;
use PhpParser\Node\Stmt\Else_;
use PhpParser\Node\Stmt\ElseIf_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Switch_;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class BinarySwitchToIfElseRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Changes switch with 2 options to if-else', [
            new CodeSample(
                <<<'CODE_SAMPLE'
switch ($foo) {
    case 'my string':
        $result = 'ok';
    break;

    default:
        $result = 'not ok';
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
if ($foo == 'my string') {
    $result = 'ok;
} else {
    $result = 'not ok';
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
        if (count($node->cases) > 2) {
            return null;
        }

        /** @var Case_ $firstCase */
        $firstCase = array_shift($node->cases);
        if ($firstCase->cond === null) {
            return null;
        }

        /** @var Case_|null $secondCase */
        $secondCase = array_shift($node->cases);

        // special case with empty first case → ||
        $isFirstCaseEmpty = $firstCase->stmts === [];
        if ($isFirstCaseEmpty && $secondCase !== null && $secondCase->cond !== null) {
            $else = new BooleanOr(new Equal($node->cond, $firstCase->cond), new Equal($node->cond, $secondCase->cond));

            $ifNode = new If_($else);
            $ifNode->stmts = $this->removeBreakNodes($secondCase->stmts);

            return $ifNode;
        }

        $ifNode = new If_(new Equal($node->cond, $firstCase->cond));
        $ifNode->stmts = $this->removeBreakNodes($firstCase->stmts);

        // just one condition
        if ($secondCase === null) {
            return $ifNode;
        }

        if ($secondCase->cond !== null) {
            // has condition
            $equalNode = new Equal($node->cond, $secondCase->cond);
            $ifNode->elseifs[] = new ElseIf_($equalNode, $this->removeBreakNodes($secondCase->stmts));
        } else {
            // defaults
            $ifNode->else = new Else_($secondCase->stmts);
        }

        return $ifNode;
    }

    /**
     * @param Stmt[] $stmts
     * @return Stmt[]
     */
    private function removeBreakNodes(array $stmts): array
    {
        foreach ($stmts as $key => $node) {
            if ($node instanceof Break_) {
                unset($stmts[$key]);
            }
        }

        return $stmts;
    }
}
