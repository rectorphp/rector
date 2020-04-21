<?php

declare(strict_types=1);

namespace Rector\PHPOffice\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\PHPOffice\ValueObject\ConditionalSetValue;

/**
 * @see https://github.com/PHPOffice/PhpSpreadsheet/blob/master/docs/topics/migration-from-PHPExcel.md#dropped-conditionally-returned-cell
 *
 * @see \Rector\PHPOffice\Tests\Rector\MethodCall\ChangeConditionalReturnedCellRector\ChangeConditionalReturnedCellRectorTest
 */
final class ChangeConditionalReturnedCellRector extends AbstractRector
{
    /**
     * @var ConditionalSetValue[]
     */
    private $conditionalSetValues = [];

    public function __construct()
    {
        $items = [
            ['setCellValue', 'getCell', 'setValue', 2, false],
            ['setCellValueByColumnAndRow', 'getCellByColumnAndRow', 'setValue', 3, true],
            ['setCellValueExplicit', 'getCell', 'setValueExplicit', 3, false],
            ['setCellValueExplicitByColumnAndRow', 'getCellByColumnAndRow', 'setValueExplicit', 4, true],
        ];

        foreach ($items as $item) {
            $this->conditionalSetValues[] = new ConditionalSetValue(...$item);
        }
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change conditional call to getCell()', [
            new CodeSample(
                <<<'PHP'
final class SomeClass
{
    public function run(): void
    {
        $worksheet = new \PHPExcel_Worksheet();
        $cell = $worksheet->setCellValue('A1', 'value', true);
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
        $cell = $worksheet->getCell('A1')->setValue('value');
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
        if (! $this->isObjectType($node->var, 'PHPExcel_Worksheet')) {
            return null;
        }

        foreach ($this->conditionalSetValues as $conditionalSetValue) {
            if (! $this->isName($node->name, $conditionalSetValue->getOldMethod())) {
                continue;
            }

            if (! isset($node->args[$conditionalSetValue->getArgPosition()])) {
                continue;
            }

            $args = $node->args;
            unset($args[$conditionalSetValue->getArgPosition()]);

            $locationArgs = [];
            $locationArgs[] = $args[0];
            unset($args[0]);

            if ($conditionalSetValue->hasRow()) {
                $locationArgs[] = $args[1];
                unset($args[1]);
            }

            $variable = clone $node->var;

            $getCellMethodCall = new MethodCall($variable, $conditionalSetValue->getNewGetMethod(), $locationArgs);
            $node->var = $getCellMethodCall;
            $node->args = $args;
            $node->name = new Identifier($conditionalSetValue->getNewSetMethod());
        }

        return $node;
    }
}
