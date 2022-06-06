<?php

declare (strict_types=1);
namespace Rector\PHPOffice\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://github.com/PHPOffice/PhpSpreadsheet/blob/master/docs/topics/migration-from-PHPExcel.md#dedicated-class-to-manipulate-coordinates
 *
 * @see \Rector\PHPOffice\Tests\Rector\MethodCall\GetDefaultStyleToGetParentRector\GetDefaultStyleToGetParentRectorTest
 */
final class GetDefaultStyleToGetParentRector extends \Rector\Core\Rector\AbstractRector
{
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Methods to (new Worksheet())->getDefaultStyle() to getParent()->getDefaultStyle()', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $worksheet = new \PHPExcel_Worksheet();
        $worksheet->getDefaultStyle();
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $worksheet = new \PHPExcel_Worksheet();
        $worksheet->getParent()->getDefaultStyle();
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
        return [\PhpParser\Node\Expr\MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$this->isObjectType($node->var, new \PHPStan\Type\ObjectType('PHPExcel_Worksheet'))) {
            return null;
        }
        if (!$this->isName($node->name, 'getDefaultStyle')) {
            return null;
        }
        $variable = clone $node->var;
        $getParentMethodCall = new \PhpParser\Node\Expr\MethodCall($variable, 'getParent');
        $node->var = $getParentMethodCall;
        return $node;
    }
}
