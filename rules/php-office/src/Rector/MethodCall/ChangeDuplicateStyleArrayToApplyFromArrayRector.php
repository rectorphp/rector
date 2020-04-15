<?php

declare(strict_types=1);

namespace Rector\PHPOffice\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see https://github.com/PHPOffice/PhpSpreadsheet/blob/master/docs/topics/migration-from-PHPExcel.md#removed-deprecated-things
 *
 * @see \Rector\PHPOffice\Tests\Rector\MethodCall\ChangeDuplicateStyleArrayToApplyFromArrayRector\ChangeDuplicateStyleArrayToApplyFromArrayRectorTest
 */
final class ChangeDuplicateStyleArrayToApplyFromArrayRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change method call duplicateStyleArray() to getStyle() + applyFromArray()', [
            new CodeSample(
                <<<'PHP'
final class SomeClass
{
    public function run(): void
    {
        $worksheet = new \PHPExcel_Worksheet();
        $worksheet->duplicateStyleArray($styles, $range, $advanced);
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
        $worksheet->getStyle($range)->applyFromArray($styles, $advanced);
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
        if (! $this->isOnClassMethodCall($node, 'PHPExcel_Worksheet', 'duplicateStyleArray')) {
            return null;
        }

        $variable = clone $node->var;

        // pop out 2nd argument
        $secondArgument = $node->args[1];
        unset($node->args[1]);

        $getStyleMethodCall = new MethodCall($variable, 'getStyle', [$secondArgument]);
        $node->var = $getStyleMethodCall;
        $node->name = new Identifier('applyFromArray');

        return $node;
    }
}
