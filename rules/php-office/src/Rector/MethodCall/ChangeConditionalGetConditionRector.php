<?php

declare(strict_types=1);

namespace Rector\PHPOffice\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\BinaryOp\Coalesce;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String_;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see https://github.com/PHPOffice/PhpSpreadsheet/blob/master/docs/topics/migration-from-PHPExcel.md#conditionalgetcondition
 *
 * @see \Rector\PHPOffice\Tests\Rector\MethodCall\ChangeConditionalGetConditionRector\ChangeConditionalGetConditionRectorTest
 */
final class ChangeConditionalGetConditionRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change argument PHPExcel_Style_Conditional->getCondition() to getConditions()', [
            new CodeSample(
                <<<'PHP'
final class SomeClass
{
    public function run(): void
    {
        $conditional = new \PHPExcel_Style_Conditional;
        $someCondition = $conditional->getCondition();
    }
}
PHP
,
                <<<'PHP'
final class SomeClass
{
    public function run(): void
    {
        $conditional = new \PHPExcel_Style_Conditional;
        $someCondition = $conditional->getConditions()[0] ?? '';
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
        return [MethodCall::class];
    }

    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isOnClassMethodCall($node, 'PHPExcel_Style_Conditional', 'getCondition')) {
            return null;
        }

        $node->name = new Identifier('getConditions');

        $dimFetch = new ArrayDimFetch($node, new LNumber(0));

        return new Coalesce($dimFetch, new String_(''));
    }
}
