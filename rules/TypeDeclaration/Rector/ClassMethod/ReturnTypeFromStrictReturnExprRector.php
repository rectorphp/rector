<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;
use PhpParser\Node\Expr\BinaryOp\Equal;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotEqual;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\Empty_;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictReturnExprRector\ReturnTypeFromStrictReturnExprRectorTest
 */
final class ReturnTypeFromStrictReturnExprRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add strict return type based on returned strict expr type', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public function run()
    {
        return $this->first() && true;
    }

    public function first()
    {
        return true;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    public function run()
    {
        return $this->first() && true;
    }

    public function first(): bool
    {
        return true;
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
        return [ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($node->returnType !== null) {
            return null;
        }
        if (!$this->hasSingleStrictReturn($node)) {
            return null;
        }
        $node->returnType = new Identifier('bool');
        return $node;
    }
    private function isStrictBoolExpr(Expr $expr) : bool
    {
        // detect strict type here :)
        if ($expr instanceof Empty_) {
            return \true;
        }
        if ($expr instanceof BooleanAnd) {
            return \true;
        }
        if ($expr instanceof BooleanOr) {
            return \true;
        }
        if ($expr instanceof Equal) {
            return \true;
        }
        if ($expr instanceof NotEqual) {
            return \true;
        }
        if ($expr instanceof Identical) {
            return \true;
        }
        if ($expr instanceof NotIdentical) {
            return \true;
        }
        return $expr instanceof ConstFetch && \in_array($expr->name->toLowerString(), ['true', 'false'], \true);
    }
    private function hasSingleStrictReturn(ClassMethod $classMethod) : bool
    {
        if ($classMethod->stmts === null) {
            return \false;
        }
        foreach ($classMethod->stmts as $stmt) {
            if (!$stmt instanceof Return_) {
                continue;
            }
            // we need exact expr return
            if (!$stmt->expr instanceof Expr) {
                return \false;
            }
            if ($this->isStrictBoolExpr($stmt->expr)) {
                return \true;
            }
        }
        return \false;
    }
}
