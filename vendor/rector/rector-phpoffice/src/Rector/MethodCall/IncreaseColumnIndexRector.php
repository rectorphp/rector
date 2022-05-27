<?php

declare (strict_types=1);
namespace Rector\PHPOffice\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\BinaryOp\Plus;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\LNumber;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://github.com/PHPOffice/PhpSpreadsheet/blob/master/docs/topics/migration-from-PHPExcel.md#column-index-based-on-1
 *
 * @see \Rector\PHPOffice\Tests\Rector\MethodCall\IncreaseColumnIndexRector\IncreaseColumnIndexRectorTest
 */
final class IncreaseColumnIndexRector extends AbstractRector
{
    /**
     * @var string
     */
    private const ALREADY_CHANGED = 'already_changed';
    /**
     * @var ObjectType[]
     */
    private $worksheetObjectTypes = [];
    public function __construct()
    {
        $this->worksheetObjectTypes = [new ObjectType('PHPExcel_Worksheet'), new ObjectType('PHPExcel_Worksheet_PageSetup')];
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Column index changed from 0 to 1 - run only ONCE! changes current value without memory', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public function run(): void
    {
        $worksheet = new \PHPExcel_Worksheet();
        $worksheet->setCellValueByColumnAndRow(0, 3, '1150');
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    public function run(): void
    {
        $worksheet = new \PHPExcel_Worksheet();
        $worksheet->setCellValueByColumnAndRow(1, 3, '1150');
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
        if (!$this->nodeTypeResolver->isObjectTypes($node->var, $this->worksheetObjectTypes)) {
            return null;
        }
        if (!$this->isName($node->name, '*ByColumnAndRow')) {
            return null;
        }
        $hasAlreadyChanged = (bool) $node->getAttribute(self::ALREADY_CHANGED, \false);
        if ($hasAlreadyChanged) {
            return null;
        }
        $node->setAttribute(self::ALREADY_CHANGED, \true);
        // increase column value
        $firstArg = $node->getArgs()[0];
        $firstArgumentValue = $firstArg->value;
        if ($firstArgumentValue instanceof LNumber) {
            ++$firstArgumentValue->value;
        }
        if ($firstArgumentValue instanceof BinaryOp) {
            $this->refactorBinaryOp($firstArgumentValue);
        }
        if ($firstArgumentValue instanceof Variable) {
            $firstArg->value = new Plus($firstArgumentValue, new LNumber(1));
        }
        return $node;
    }
    private function refactorBinaryOp(BinaryOp $binaryOp) : void
    {
        if ($binaryOp->left instanceof LNumber) {
            ++$binaryOp->left->value;
            return;
        }
        if ($binaryOp->right instanceof LNumber) {
            ++$binaryOp->right->value;
        }
    }
}
