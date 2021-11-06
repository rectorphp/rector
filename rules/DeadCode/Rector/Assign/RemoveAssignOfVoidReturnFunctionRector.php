<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\Assign;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PHPStan\Type\VoidType;
use Rector\Core\Rector\AbstractRector;
use Rector\DeadCode\NodeAnalyzer\ExprUsedInNextNodeAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\Assign\RemoveAssignOfVoidReturnFunctionRector\RemoveAssignOfVoidReturnFunctionRectorTest
 */
final class RemoveAssignOfVoidReturnFunctionRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var \Rector\DeadCode\NodeAnalyzer\ExprUsedInNextNodeAnalyzer
     */
    private $exprUsedInNextNodeAnalyzer;
    public function __construct(\Rector\DeadCode\NodeAnalyzer\ExprUsedInNextNodeAnalyzer $exprUsedInNextNodeAnalyzer)
    {
        $this->exprUsedInNextNodeAnalyzer = $exprUsedInNextNodeAnalyzer;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Remove assign of void function/method to variable', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $value = $this->getOne();
    }

    private function getOne(): void
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $this->getOne();
    }

    private function getOne(): void
    {
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
        return [\PhpParser\Node\Expr\Assign::class];
    }
    /**
     * @param Assign $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$node->expr instanceof \PhpParser\Node\Expr\FuncCall && !$node->expr instanceof \PhpParser\Node\Expr\MethodCall && !$node->expr instanceof \PhpParser\Node\Expr\StaticCall) {
            return null;
        }
        if (!$node->var instanceof \PhpParser\Node\Expr\Variable) {
            return null;
        }
        if ($this->exprUsedInNextNodeAnalyzer->isUsed($node->var, \true)) {
            return null;
        }
        $exprType = $this->nodeTypeResolver->getType($node->expr);
        if (!$exprType instanceof \PHPStan\Type\VoidType) {
            return null;
        }
        return $node->expr;
    }
}
