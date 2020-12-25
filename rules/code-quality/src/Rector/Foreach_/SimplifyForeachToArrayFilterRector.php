<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Rector\Foreach_;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\If_;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\CodeQuality\Tests\Rector\Foreach_\SimplifyForeachToArrayFilterRector\SimplifyForeachToArrayFilterRectorTest
 */
final class SimplifyForeachToArrayFilterRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Simplify foreach with function filtering to array filter',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
$directories = [];
$possibleDirectories = [];
foreach ($possibleDirectories as $possibleDirectory) {
    if (file_exists($possibleDirectory)) {
        $directories[] = $possibleDirectory;
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
$possibleDirectories = [];
$directories = array_filter($possibleDirectories, 'file_exists');
CODE_SAMPLE
                ),

            ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Foreach_::class];
    }

    /**
     * @param Foreach_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }

        /** @var If_ $ifNode */
        $ifNode = $node->stmts[0];

        /** @var FuncCall $funcCallNode */
        $funcCallNode = $ifNode->cond;
        if (count($ifNode->stmts) !== 1) {
            return null;
        }

        if (count($funcCallNode->args) !== 1) {
            return null;
        }

        if (! $this->areNodesEqual($funcCallNode->args[0], $node->valueVar)) {
            return null;
        }

        if (! $ifNode->stmts[0] instanceof Expression) {
            return null;
        }

        $onlyNodeInIf = $ifNode->stmts[0]->expr;
        if (! $onlyNodeInIf instanceof Assign) {
            return null;
        }

        if (! $onlyNodeInIf->var instanceof ArrayDimFetch) {
            return null;
        }

        if (! $this->areNodesEqual($onlyNodeInIf->expr, $node->valueVar)) {
            return null;
        }

        $name = $this->getName($funcCallNode);
        if ($name === null) {
            return null;
        }

        return $this->createAssignNode($node, $name, $onlyNodeInIf->var);
    }

    private function shouldSkip(Foreach_ $foreach): bool
    {
        if (count($foreach->stmts) !== 1) {
            return true;
        }

        if (! $foreach->stmts[0] instanceof If_) {
            return true;
        }

        /** @var If_ $ifNode */
        $ifNode = $foreach->stmts[0];

        if ($ifNode->else !== null) {
            return true;
        }

        if ($ifNode->elseifs !== []) {
            return true;
        }

        return ! $ifNode->cond instanceof FuncCall;
    }

    private function createAssignNode(Foreach_ $foreach, string $name, ArrayDimFetch $arrayDimFetch): Assign
    {
        $string = new String_($name);

        $args = [new Arg($foreach->expr), new Arg($string)];
        $arrayFilterFuncCall = new FuncCall(new Name('array_filter'), $args);

        return new Assign($arrayDimFetch->var, $arrayFilterFuncCall);
    }
}
