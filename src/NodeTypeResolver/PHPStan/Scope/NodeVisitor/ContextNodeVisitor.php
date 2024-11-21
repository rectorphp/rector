<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\PHPStan\Scope\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Attribute;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\PostDec;
use PhpParser\Node\Expr\PostInc;
use PhpParser\Node\Expr\PreDec;
use PhpParser\Node\Expr\PreInc;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Break_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Do_;
use PhpParser\Node\Stmt\Else_;
use PhpParser\Node\Stmt\ElseIf_;
use PhpParser\Node\Stmt\For_;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Switch_;
use PhpParser\Node\Stmt\Unset_;
use PhpParser\Node\Stmt\While_;
use PhpParser\NodeVisitor;
use PhpParser\NodeVisitorAbstract;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\PHPStan\Scope\Contract\NodeVisitor\ScopeResolverNodeVisitorInterface;
use Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser;
final class ContextNodeVisitor extends NodeVisitorAbstract implements ScopeResolverNodeVisitorInterface
{
    /**
     * @readonly
     */
    private SimpleCallableNodeTraverser $simpleCallableNodeTraverser;
    public function __construct(SimpleCallableNodeTraverser $simpleCallableNodeTraverser)
    {
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
    }
    public function enterNode(Node $node) : ?Node
    {
        if ($node instanceof For_ || $node instanceof Foreach_ || $node instanceof While_ || $node instanceof Do_ || $node instanceof Switch_) {
            $this->processContextInLoop($node);
            return null;
        }
        if ($node instanceof ArrayDimFetch) {
            $this->processInsideArrayDimFetch($node);
            return null;
        }
        if ($node instanceof Unset_) {
            $this->processContextInUnset($node);
            return null;
        }
        if ($node instanceof Attribute) {
            $this->processContextInAttribute($node);
            return null;
        }
        if ($node instanceof If_ || $node instanceof Else_ || $node instanceof ElseIf_) {
            $this->processContextInIf($node);
            return null;
        }
        if ($node instanceof Arg) {
            $node->value->setAttribute(AttributeKey::IS_ARG_VALUE, \true);
            return null;
        }
        if ($node instanceof Param) {
            $node->var->setAttribute(AttributeKey::IS_PARAM_VAR, \true);
            return null;
        }
        if ($node instanceof PostDec || $node instanceof PostInc || $node instanceof PreDec || $node instanceof PreInc) {
            $node->var->setAttribute(AttributeKey::IS_INCREMENT_OR_DECREMENT, \true);
            return null;
        }
        if ($node instanceof BooleanAnd) {
            $node->right->setAttribute(AttributeKey::IS_RIGHT_AND, \true);
            return null;
        }
        $this->processContextInClass($node);
        return null;
    }
    private function processInsideArrayDimFetch(ArrayDimFetch $arrayDimFetch) : void
    {
        if ($arrayDimFetch->var instanceof PropertyFetch || $arrayDimFetch->var instanceof StaticPropertyFetch) {
            $arrayDimFetch->var->setAttribute(AttributeKey::INSIDE_ARRAY_DIM_FETCH, \true);
        }
    }
    private function processContextInClass(Node $node) : void
    {
        if ($node instanceof Class_) {
            if ($node->extends instanceof FullyQualified) {
                $node->extends->setAttribute(AttributeKey::IS_CLASS_EXTENDS, \true);
            }
            foreach ($node->implements as $implement) {
                $implement->setAttribute(AttributeKey::IS_CLASS_IMPLEMENT, \true);
            }
        }
    }
    private function processContextInAttribute(Attribute $attribute) : void
    {
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($attribute->args, static function (Node $subNode) {
            if ($subNode instanceof Array_) {
                $subNode->setAttribute(AttributeKey::IS_ARRAY_IN_ATTRIBUTE, \true);
            }
            return null;
        });
    }
    private function processContextInUnset(Unset_ $unset) : void
    {
        foreach ($unset->vars as $var) {
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
     * @param \PhpParser\Node\Stmt\For_|\PhpParser\Node\Stmt\Foreach_|\PhpParser\Node\Stmt\While_|\PhpParser\Node\Stmt\Do_|\PhpParser\Node\Stmt\Switch_ $node
     */
    private function processContextInLoop($node) : void
    {
        if ($node instanceof Foreach_) {
            if ($node->keyVar instanceof Variable) {
                $node->keyVar->setAttribute(AttributeKey::IS_VARIABLE_LOOP, \true);
            }
            $node->valueVar->setAttribute(AttributeKey::IS_VARIABLE_LOOP, \true);
        }
        $stmts = $node instanceof Switch_ ? $node->cases : $node->stmts;
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($stmts, static function (Node $subNode) : ?int {
            if ($subNode instanceof Class_ || $subNode instanceof Function_ || $subNode instanceof Closure) {
                return NodeVisitor::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
            }
            if ($subNode instanceof If_ || $subNode instanceof Break_) {
                $subNode->setAttribute(AttributeKey::IS_IN_LOOP, \true);
            }
            return null;
        });
    }
}
