<?php

declare(strict_types=1);

namespace Rector\PHPOffice\Rector\StaticCall;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name\FullyQualified;
use Rector\Core\Rector\AbstractRector;

/**
 * @see https://github.com/PHPOffice/PhpSpreadsheet/blob/master/docs/topics/migration-from-PHPExcel.md#datatypedatatypeforvalue
 *
 * @see \Rector\PHPOffice\Tests\Rector\StaticCall\ChangeDataTypeForValueRector\ChangeDataTypeForValueRectorTest
 */
final class ChangeDataTypeForValueRector extends AbstractRector
{
    public function getRuleDefinition(): \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition(
            'Change argument DataType::dataTypeForValue() to DefaultValueBinder',
            [
                new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(
                    <<<'CODE_SAMPLE'
final class SomeClass
{
    public function run(): void
    {
        $type = \PHPExcel_Cell_DataType::dataTypeForValue('value');
    }
}
CODE_SAMPLE
,
                    <<<'CODE_SAMPLE'
final class SomeClass
{
    public function run(): void
    {
        $type = \PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder::dataTypeForValue('value');
    }
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
        return [StaticCall::class];
    }

    /**
     * @param StaticCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isStaticCallNamed($node, 'PHPExcel_Cell_DataType', 'dataTypeForValue')) {
            return null;
        }

        $node->class = new FullyQualified('PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder');

        return $node;
    }
}
