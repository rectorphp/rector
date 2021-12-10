<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\Foreach_;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\If_;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\Foreach_\SimplifyForeachToArrayFilterRector\SimplifyForeachToArrayFilterRectorTest
 */
final class SimplifyForeachToArrayFilterRector extends \Rector\Core\Rector\AbstractRector
{
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Simplify foreach with function filtering to array filter', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
$directories = [];
$possibleDirectories = [];
foreach ($possibleDirectories as $possibleDirectory) {
    if (file_exists($possibleDirectory)) {
        $directories[] = $possibleDirectory;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$possibleDirectories = [];
$directories = array_filter($possibleDirectories, 'file_exists');
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\Foreach_::class];
    }
    /**
     * @param Foreach_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }
        /** @var If_ $ifNode */
        $ifNode = $node->stmts[0];
        /** @var FuncCall $funcCallNode */
        $funcCallNode = $ifNode->cond;
        if (\count($ifNode->stmts) !== 1) {
            return null;
        }
        if (!$this->isSimpleCall($funcCallNode, $node)) {
            return null;
        }
        if (!$ifNode->stmts[0] instanceof \PhpParser\Node\Stmt\Expression) {
            return null;
        }
        $onlyNodeInIf = $ifNode->stmts[0]->expr;
        if (!$onlyNodeInIf instanceof \PhpParser\Node\Expr\Assign) {
            return null;
        }
        $arrayDimFetch = $onlyNodeInIf->var;
        if (!$arrayDimFetch instanceof \PhpParser\Node\Expr\ArrayDimFetch) {
            return null;
        }
        if (!$this->nodeComparator->areNodesEqual($onlyNodeInIf->expr, $node->valueVar)) {
            return null;
        }
        $name = $this->getName($funcCallNode);
        if ($name === null) {
            return null;
        }
        if (!$this->isArrayDimFetchInForLoop($node, $arrayDimFetch)) {
            return null;
        }
        return $this->createAssignNode($node, $name, $arrayDimFetch);
    }
    private function shouldSkip(\PhpParser\Node\Stmt\Foreach_ $foreach) : bool
    {
        if (\count($foreach->stmts) !== 1) {
            return \true;
        }
        if (!$foreach->stmts[0] instanceof \PhpParser\Node\Stmt\If_) {
            return \true;
        }
        /** @var If_ $ifNode */
        $ifNode = $foreach->stmts[0];
        if ($ifNode->else !== null) {
            return \true;
        }
        if ($ifNode->elseifs !== []) {
            return \true;
        }
        return !$ifNode->cond instanceof \PhpParser\Node\Expr\FuncCall;
    }
    private function createAssignNode(\PhpParser\Node\Stmt\Foreach_ $foreach, string $name, \PhpParser\Node\Expr\ArrayDimFetch $arrayDimFetch) : \PhpParser\Node\Expr\Assign
    {
        $string = new \PhpParser\Node\Scalar\String_($name);
        $args = [new \PhpParser\Node\Arg($foreach->expr), new \PhpParser\Node\Arg($string)];
        $arrayFilterFuncCall = new \PhpParser\Node\Expr\FuncCall(new \PhpParser\Node\Name('array_filter'), $args);
        return new \PhpParser\Node\Expr\Assign($arrayDimFetch->var, $arrayFilterFuncCall);
    }
    private function isArrayDimFetchInForLoop(\PhpParser\Node\Stmt\Foreach_ $foreach, \PhpParser\Node\Expr\ArrayDimFetch $arrayDimFetch) : bool
    {
        $loopVar = $foreach->expr;
        if (!$loopVar instanceof \PhpParser\Node\Expr\Variable) {
            return \false;
        }
        $varThatIsModified = $arrayDimFetch->var;
        if (!$varThatIsModified instanceof \PhpParser\Node\Expr\Variable) {
            return \false;
        }
        return $loopVar->name !== $varThatIsModified->name;
    }
    private function isSimpleCall(\PhpParser\Node\Expr\FuncCall $funcCall, \PhpParser\Node\Stmt\Foreach_ $foreach) : bool
    {
        if (\count($funcCall->args) !== 1) {
            return \false;
        }
        return $this->nodeComparator->areNodesEqual($funcCall->args[0], $foreach->valueVar);
    }
}
