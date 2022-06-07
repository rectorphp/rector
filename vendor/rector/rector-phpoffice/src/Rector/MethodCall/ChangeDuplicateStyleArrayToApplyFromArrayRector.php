<?php

declare (strict_types=1);
namespace Rector\PHPOffice\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://github.com/PHPOffice/PhpSpreadsheet/blob/master/docs/topics/migration-from-PHPExcel.md#removed-deprecated-things
 *
 * @see \Rector\PHPOffice\Tests\Rector\MethodCall\ChangeDuplicateStyleArrayToApplyFromArrayRector\ChangeDuplicateStyleArrayToApplyFromArrayRectorTest
 */
final class ChangeDuplicateStyleArrayToApplyFromArrayRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change method call duplicateStyleArray() to getStyle() + applyFromArray()', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public function run(): void
    {
        $worksheet = new \PHPExcel_Worksheet();
        $worksheet->duplicateStyleArray($styles, $range, $advanced);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    public function run(): void
    {
        $worksheet = new \PHPExcel_Worksheet();
        $worksheet->getStyle($range)->applyFromArray($styles, $advanced);
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
        return [MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->isObjectType($node->var, new ObjectType('PHPExcel_Worksheet'))) {
            return null;
        }
        if (!$this->nodeNameResolver->isName($node->name, 'duplicateStyleArray')) {
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
