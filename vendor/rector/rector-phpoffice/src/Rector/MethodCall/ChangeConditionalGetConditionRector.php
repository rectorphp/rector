<?php

declare (strict_types=1);
namespace Rector\PHPOffice\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\BinaryOp\Coalesce;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String_;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://github.com/PHPOffice/PhpSpreadsheet/blob/master/docs/topics/migration-from-PHPExcel.md#conditionalgetcondition
 *
 * @see \Rector\PHPOffice\Tests\Rector\MethodCall\ChangeConditionalGetConditionRector\ChangeConditionalGetConditionRectorTest
 */
final class ChangeConditionalGetConditionRector extends \Rector\Core\Rector\AbstractRector
{
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Change argument PHPExcel_Style_Conditional->getCondition() to getConditions()', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public function run(): void
    {
        $conditional = new \PHPExcel_Style_Conditional;
        $someCondition = $conditional->getCondition();
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    public function run(): void
    {
        $conditional = new \PHPExcel_Style_Conditional;
        $someCondition = $conditional->getConditions()[0] ?? '';
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
        if (!$this->isObjectType($node->var, new \PHPStan\Type\ObjectType('PHPExcel_Style_Conditional'))) {
            return null;
        }
        if (!$this->isName($node->name, 'getCondition')) {
            return null;
        }
        $node->name = new \PhpParser\Node\Identifier('getConditions');
        $arrayDimFetch = new \PhpParser\Node\Expr\ArrayDimFetch($node, new \PhpParser\Node\Scalar\LNumber(0));
        return new \PhpParser\Node\Expr\BinaryOp\Coalesce($arrayDimFetch, new \PhpParser\Node\Scalar\String_(''));
    }
}
