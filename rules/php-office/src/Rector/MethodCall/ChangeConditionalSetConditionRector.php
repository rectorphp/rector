<?php

declare(strict_types=1);

namespace Rector\PHPOffice\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\Cast\Array_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PHPStan\Type\ArrayType;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see https://github.com/PHPOffice/PhpSpreadsheet/blob/master/docs/topics/migration-from-PHPExcel.md#conditionalsetcondition
 *
 * @see \Rector\PHPOffice\Tests\Rector\MethodCall\ChangeConditionalSetConditionRector\ChangeConditionalSetConditionRectorTest
 */
final class ChangeConditionalSetConditionRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change argument PHPExcel_Style_Conditional->setCondition() to setConditions()', [
            new CodeSample(
                <<<'PHP'
final class SomeClass
{
    public function run(): void
    {
        $conditional = new \PHPExcel_Style_Conditional;
        $someCondition = $conditional->setCondition(1);
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
        $someCondition = $conditional->setConditions((array) 1);
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
        if (! $this->isOnClassMethodCall($node, 'PHPExcel_Style_Conditional', 'setCondition')) {
            return null;
        }

        $node->name = new Identifier('setConditions');
        $this->castArgumentToArrayIfNotArrayType($node);

        return $node;
    }

    private function castArgumentToArrayIfNotArrayType(MethodCall $methodCall): void
    {
        $firstArgumentValue = $methodCall->args[0]->value;
        $firstArgumentStaticType = $this->getStaticType($firstArgumentValue);
        if ($firstArgumentStaticType instanceof ArrayType) {
            return;
        }

        // cast to array if not an array
        $methodCall->args[0]->value = new Array_($firstArgumentValue);
    }
}
