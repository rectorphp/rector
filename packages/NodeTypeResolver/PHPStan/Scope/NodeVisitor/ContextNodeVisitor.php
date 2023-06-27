<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\PHPStan\Scope\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Attribute;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\Isset_;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Break_;
use PhpParser\Node\Stmt\Do_;
use PhpParser\Node\Stmt\Else_;
use PhpParser\Node\Stmt\ElseIf_;
use PhpParser\Node\Stmt\For_;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\Unset_;
use PhpParser\Node\Stmt\While_;
use PhpParser\NodeVisitorAbstract;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\PHPStan\Scope\Contract\NodeVisitor\ScopeResolverNodeVisitorInterface;
use Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser;
final class ContextNodeVisitor extends NodeVisitorAbstract implements ScopeResolverNodeVisitorInterface
{
    /**
     * @readonly
     * @var \Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser
     */
    private $simpleCallableNodeTraverser;
    public function __construct(SimpleCallableNodeTraverser $simpleCallableNodeTraverser)
    {
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
    }
    public function enterNode(Node $node) : ?Node
    {
        if ($node instanceof For_ || $node instanceof Foreach_ || $node instanceof While_ || $node instanceof Do_) {
            $this->processContextInLoop($node);
            return null;
        }
        if ($node instanceof Isset_ || $node instanceof Unset_) {
            $this->processContextInIssetOrUnset($node);
            return null;
        }
        if ($node instanceof Attribute) {
            $this->simpleCallableNodeTraverser->traverseNodesWithCallable($node->args, static function (Node $subNode) {
                if ($subNode instanceof Array_) {
                    $subNode->setAttribute(AttributeKey::IS_ARRAY_IN_ATTRIBUTE, \true);
                }
                return null;
            });
        }
        if ($node instanceof If_ || $node instanceof Else_ || $node instanceof ElseIf_) {
            $this->processContextInIf($node);
            return null;
        }
        if ($node instanceof Return_ && $node->expr instanceof Expr) {
            $node->expr->setAttribute(AttributeKey::IS_RETURN_EXPR, \true);
        }
        if ($node instanceof Arg) {
            $node->value->setAttribute(AttributeKey::IS_ARG_VALUE, \true);
        }
        if ($node instanceof Param) {
            $this->processContextParam($node);
        }
        return null;
    }
    private function processContextParam(Param $param) : void
    {
        if (!$param->type instanceof Node) {
            return;
        }
        $param->type->setAttribute(AttributeKey::IS_PARAM_TYPE, \true);
    }
    /**
     * @param \PhpParser\Node\Expr\Isset_|\PhpParser\Node\Stmt\Unset_ $node
     */
    private function processContextInIssetOrUnset($node) : void
    {
        if ($node instanceof Isset_) {
            foreach ($node->vars as $var) {
                $var->setAttribute(AttributeKey::IS_ISSET_VAR, \true);
            }
            return;
        }
        foreach ($node->vars as $var) {
            $var->setAttribute(AttributeKey::IS_UNSET_VAR, \true);
        }
    }
    /**
     * @param \PhpParser\Node\Stmt\If_|\PhpParser\Node\Stmt\Else_|\PhpParser\Node\Stmt\ElseIf_ $node
     */
    private function processContextInIf($node) : void
    {
        foreach ($node->stmts as $stmt) {
            if ($stmt instanceof Break_) {
                $stmt->setAttribute(AttributeKey::IS_IN_IF, \true);
            }
        }
    }
    /**
     * @param Stmt[] $node
     */
    private function processContextInLoop($node) : void
    {
        $stmts = $node instanceof Stmt ? $node->stmts : $node;
        foreach ($stmts as $stmt) {
            if ($stmt instanceof Break_) {
                $stmt->setAttribute(AttributeKey::IS_IN_LOOP, \true);
                continue;
            }
            if ($stmt instanceof If_) {
                $stmt->setAttribute(AttributeKey::IS_IN_LOOP, \true);
                $this->processContextInLoop($stmt->stmts);
                foreach ($stmt->elseifs as $elseif) {
                    $this->processContextInLoop($elseif->stmts);
                }
                if ($stmt->else instanceof Else_) {
                    $this->processContextInLoop($stmt->else->stmts);
                }
            }
        }
    }
}
