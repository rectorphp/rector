<?php

declare(strict_types=1);

namespace Rector\PHPOffice\Rector\StaticCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\StaticCall;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see https://github.com/PHPOffice/PhpSpreadsheet/blob/master/docs/topics/migration-from-PHPExcel.md#rendering-charts
 *
 * @see \Rector\PHPOffice\Tests\Rector\StaticCall\ChangeChartRendererRector\ChangeChartRendererRectorTest
 */
final class ChangeChartRendererRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change chart renderer', [
            new CodeSample(
                <<<'PHP'
final class SomeClass
{
    public function run(): void
    {
        \PHPExcel_Settings::setChartRenderer($rendererName, $rendererLibraryPath);
    }
}
PHP
,
                <<<'PHP'
final class SomeClass
{
    public function run(): void
    {
        \PHPExcel_Settings::setChartRenderer(\PhpOffice\PhpSpreadsheet\Chart\Renderer\JpGraph::class);
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
        return [StaticCall::class];
    }

    /**
     * @param StaticCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isStaticCallNamed($node, 'PHPExcel_Settings', 'setChartRenderer')) {
            return null;
        }

        if (count($node->args) === 1) {
            return null;
        }

        $arg = new Arg($this->createClassConstantReference('PhpOffice\PhpSpreadsheet\Chart\Renderer\JpGraph'));
        $node->args = [$arg];

        return $node;
    }
}
