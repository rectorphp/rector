<?php

declare(strict_types=1);

namespace Rector\PHPOffice\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\BinaryOp\Plus;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Stmt\For_;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see https://github.com/PHPOffice/PhpSpreadsheet/blob/master/docs/topics/migration-from-PHPExcel.md#column-index-based-on-1
 *
 * @see \Rector\PHPOffice\Tests\Rector\MethodCall\IncreaseColumnIndexRector\IncreaseColumnIndexRectorTest
 */
final class IncreaseColumnIndexRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Column index changed from 0 to 1 - run only ONCE! changes current value without memory',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
final class SomeClass
{
    public function run(): void
    {
        $worksheet = new \PHPExcel_Worksheet();
        $worksheet->setCellValueByColumnAndRow(0, 3, '1150');
    }
}
CODE_SAMPLE
,
                    <<<'CODE_SAMPLE'
final class SomeClass
{
    public function run(): void
    {
        $worksheet = new \PHPExcel_Worksheet();
        $worksheet->setCellValueByColumnAndRow(1, 3, '1150');
    }
}
CODE_SAMPLE
            ),
            ]
        );
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class];
    }

    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isObjectTypes($node->var, ['PHPExcel_Worksheet', 'PHPExcel_Worksheet_PageSetup'])) {
            return null;
        }

        if (! $this->isName($node->name, '*ByColumnAndRow')) {
            return null;
        }

        // increase column value
        $firstArgumentValue = $node->args[0]->value;
        if ($firstArgumentValue instanceof LNumber) {
            ++$firstArgumentValue->value;
        }

        if ($firstArgumentValue instanceof BinaryOp) {
            $this->refactorBinaryOp($firstArgumentValue);
        }

        if ($firstArgumentValue instanceof Variable) {
            // check if for() value, rather update that
            $lNumber = $this->findPreviousForWithVariable($firstArgumentValue);
            if (! $lNumber instanceof LNumber) {
                $node->args[0]->value = new Plus($firstArgumentValue, new LNumber(1));
                return null;
            }

            ++$lNumber->value;
        }

        return $node;
    }

    private function refactorBinaryOp(BinaryOp $binaryOp): void
    {
        if ($binaryOp->left instanceof LNumber) {
            ++$binaryOp->left->value;
            return;
        }

        if ($binaryOp->right instanceof LNumber) {
            ++$binaryOp->right->value;
            return;
        }
    }

    private function findPreviousForWithVariable(Variable $variable): ?LNumber
    {
        $for = $this->betterNodeFinder->findFirstPreviousOfTypes($variable, [For_::class]);
        if (! $for instanceof For_) {
            return null;
        }

        $variableName = $this->getName($variable);
        if ($variableName === null) {
            return null;
        }

        $assignVariable = $this->findVariableAssignName($for->init, $variableName);
        if (! $assignVariable instanceof Assign) {
            return null;
        }

        if ($assignVariable->expr instanceof LNumber) {
            return $assignVariable->expr;
        }

        return null;
    }

    /**
     * @param Node|Node[] $node
     */
    private function findVariableAssignName($node, string $variableName): ?Node
    {
        return $this->betterNodeFinder->findFirst((array) $node, function (Node $node) use ($variableName): bool {
            if (! $node instanceof Assign) {
                return false;
            }

            return $this->isVariableName($node->var, $variableName);
        });
    }
}
