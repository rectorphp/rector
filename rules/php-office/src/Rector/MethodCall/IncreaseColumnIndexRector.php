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
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\For_;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see https://github.com/PHPOffice/PhpSpreadsheet/blob/master/docs/topics/migration-from-PHPExcel.md#column-index-based-on-1
 *
 * @see \Rector\PHPOffice\Tests\Rector\MethodCall\IncreaseColumnIndexRector\IncreaseColumnIndexRectorTest
 */
final class IncreaseColumnIndexRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Column index changed from 0 to 1 - run only ONCE! changes current value without memory',
            [
                new CodeSample(
                    <<<'PHP'
final class SomeClass
{
    public function run(): void
    {
        $worksheet = new \PHPExcel_Worksheet();
        $worksheet->setCellValueByColumnAndRow(0, 3, '1150');
    }
}
PHP
,
                    <<<'PHP'
final class SomeClass
{
    public function run(): void
    {
        $worksheet = new \PHPExcel_Worksheet();
        $worksheet->setCellValueByColumnAndRow(1, 3, '1150');
    }
}
PHP

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
        if (! $this->isObjectType($node->var, 'PHPExcel_Worksheet')) {
            return null;
        }

        if (! $this->isName($node->name, '*ByColumnAndRow')) {
            return null;
        }

        // increase column value
        $firstArumentValue = $node->args[0]->value;
        if ($firstArumentValue instanceof LNumber) {
            ++$firstArumentValue->value;
        }

        if ($firstArumentValue instanceof BinaryOp) {
            $this->refactorBinaryOp($firstArumentValue);
        }

        if ($firstArumentValue instanceof Variable) {
            $parentNode = $this->getParentNode($node);

            if (! $parentNode instanceof For_) {
                $node->args[0]->value = new Plus($firstArumentValue, new LNumber(1));
                return null;
            }

            // check if for() value, rather update that
            $this->refactorFor($firstArumentValue, $parentNode);
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

    /**
     * @param Node|Node[] $node
     */
    private function findVariableAssignName($node, string $variableName): ?Assign
    {
        return $this->betterNodeFinder->findFirst((array) $node, function (Node $node) use ($variableName) {
            if (! $node instanceof Assign) {
                return false;
            }

            if (! $node->var instanceof Variable) {
                return false;
            }

            return $this->isName($node->var, $variableName);
        });
    }

    private function getParentNode(Node $node): ?Node
    {
        $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
        if ($parentNode instanceof Expression) {
            $parentNode = $parentNode->getAttribute(AttributeKey::PARENT_NODE);
        }

        return $parentNode;
    }

    private function refactorFor(Variable $variable, For_ $for): void
    {
        $variableName = $this->getName($variable);

        // nothing we can do
        if ($variableName === null) {
            return;
        }

        $assignVariable = $this->findVariableAssignName($for->init, $variableName);
        if ($assignVariable === null) {
            return;
        }

        if ($assignVariable->expr instanceof LNumber) {
            $number = $this->getValue($assignVariable->expr);
            $assignVariable->expr = new LNumber($number + 1);
        }
    }
}
