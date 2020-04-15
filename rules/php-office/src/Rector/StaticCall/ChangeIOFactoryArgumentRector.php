<?php

declare(strict_types=1);

namespace Rector\PHPOffice\Rector\StaticCall;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Scalar\String_;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see https://github.com/PHPOffice/PhpSpreadsheet/blob/master/docs/topics/migration-from-PHPExcel.md#renamed-readers-and-writers
 *
 * @see \Rector\PHPOffice\Tests\Rector\StaticCall\ChangeIOFactoryArgumentRector\ChangeIOFactoryArgumentRectorTest
 */
final class ChangeIOFactoryArgumentRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private const OLD_TO_NEW_TYPE = [
        'CSV' => 'Csv',
        'Excel2003XML' => 'Xml',
        'Excel2007' => 'Xlsx',
        'Excel5' => 'Xls',
        'Gnumeric' => 'Gnumeric',
        'HTML' => 'Html',
        'OOCalc' => 'Ods',
        'OpenDocument' => 'Ods',
        'PDF' => 'Pdf',
        'SYLK' => 'Slk',
    ];

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Change argument of PHPExcel_IOFactory::createReader(), PHPExcel_IOFactory::createWriter() and PHPExcel_IOFactory::identify()',
            [
                new CodeSample(
                    <<<'PHP'
final class SomeClass
{
    public function run(): void
    {
        $writer = \PHPExcel_IOFactory::createWriter('CSV');
    }
}
PHP
,
                    <<<'PHP'
final class SomeClass
{
    public function run(): void
    {
        $writer = \PHPExcel_IOFactory::createWriter('Csv');
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
        return [StaticCall::class];
    }

    /**
     * @param StaticCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isStaticCallsNamed($node, 'PHPExcel_IOFactory', ['createReader', 'createWriter', 'identify'])) {
            return null;
        }

        $firstArgumentValue = $this->getValue($node->args[0]->value);
        $newValue = self::OLD_TO_NEW_TYPE[$firstArgumentValue] ?? null;
        if ($newValue === null) {
            return null;
        }

        $node->args[0]->value = new String_($newValue);

        return $node;
    }
}
