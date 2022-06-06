<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\PHPOffice\Rector\StaticCall;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr\StaticCall;
use RectorPrefix20220606\PhpParser\Node\Scalar\String_;
use RectorPrefix20220606\PHPStan\Type\ObjectType;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://github.com/PHPOffice/PhpSpreadsheet/blob/master/docs/topics/migration-from-PHPExcel.md#renamed-readers-and-writers
 *
 * @see \Rector\PHPOffice\Tests\Rector\StaticCall\ChangeIOFactoryArgumentRector\ChangeIOFactoryArgumentRectorTest
 */
final class ChangeIOFactoryArgumentRector extends AbstractRector
{
    /**
     * @var array<string, string>
     */
    private const OLD_TO_NEW_TYPE = ['CSV' => 'Csv', 'Excel2003XML' => 'Xml', 'Excel2007' => 'Xlsx', 'Excel5' => 'Xls', 'Gnumeric' => 'Gnumeric', 'HTML' => 'Html', 'OOCalc' => 'Ods', 'OpenDocument' => 'Ods', 'PDF' => 'Pdf', 'SYLK' => 'Slk'];
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change argument of PHPExcel_IOFactory::createReader(), PHPExcel_IOFactory::createWriter() and PHPExcel_IOFactory::identify()', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public function run(): void
    {
        $writer = \PHPExcel_IOFactory::createWriter('CSV');
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    public function run(): void
    {
        $writer = \PHPExcel_IOFactory::createWriter('Csv');
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [StaticCall::class];
    }
    /**
     * @param StaticCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->isObjectType($node->class, new ObjectType('PHPExcel_IOFactory'))) {
            return null;
        }
        if (!$this->isNames($node->name, ['createReader', 'createWriter', 'identify'])) {
            return null;
        }
        $firstArgumentValue = $this->valueResolver->getValue($node->args[0]->value);
        $newValue = self::OLD_TO_NEW_TYPE[$firstArgumentValue] ?? null;
        if ($newValue === null) {
            return null;
        }
        $node->args[0]->value = new String_($newValue);
        return $node;
    }
}
