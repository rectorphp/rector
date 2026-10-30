<?php

declare (strict_types=1);
namespace Rector\PhpParser\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\ArrayItem;
use PhpParser\Node\Attribute;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\Isset_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PostDec;
use PhpParser\Node\Expr\PostInc;
use PhpParser\Node\Expr\PreDec;
use PhpParser\Node\Expr\PreInc;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Param;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Break_;
use PhpParser\Node\Stmt\Catch_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Do_;
use PhpParser\Node\Stmt\Else_;
use PhpParser\Node\Stmt\ElseIf_;
use PhpParser\Node\Stmt\Finally_;
use PhpParser\Node\Stmt\For_;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Switch_;
use PhpParser\Node\Stmt\TryCatch;
use PhpParser\Node\Stmt\Unset_;
use PhpParser\Node\Stmt\While_;
use PhpParser\NodeVisitorAbstract;
use Rector\Contract\PhpParser\DecoratingNodeVisitorInterface;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class ContextNodeVisitor extends NodeVisitorAbstract implements DecoratingNodeVisitorInterface
{
    /**
     * Context is tracked by depth during the main traversal, instead of traversing
     * the loop, try and attribute children again on every enter
     */
    private int $loopDepth = 0;
    private int $tryDepth = 0;
    private int $attributeDepth = 0;
    /**
     * @var array<array{int, int}>
     */
    private array $outerLoopAndTryDepths = [];
    /**
     * @param Node[] $nodes
     * @return null
     */
    public function beforeTraverse(array $nodes)
    {
        $this->loopDepth = 0;
        $this->tryDepth = 0;
        $this->attributeDepth = 0;
        $this->outerLoopAndTryDepths = [];
        return null;
    }
    public function enterNode(Node $node): ?Node
    {
        $this->decorateEnclosingContext($node);
        if ($node instanceof For_ || $node instanceof Foreach_ || $node instanceof While_ || $node instanceof Do_ || $node instanceof Switch_) {
            $this->processContextInLoop($node);
            ++$this->loopDepth;
            return null;
        }
        if ($node instanceof ArrayDimFetch) {
            $this->processInsideArrayDimFetch($node);
            return null;
        }
        if ($node instanceof Unset_) {
            foreach ($node->vars as $var) {
                $var->setAttribute(AttributeKey::IS_UNSET_VAR, \true);
            }
            return null;
        }
        if ($node instanceof TryCatch) {
            ++$this->tryDepth;
            return null;
        }
        if ($node instanceof Isset_) {
            foreach ($node->vars as $var) {
                $var->setAttribute(AttributeKey::IS_ISSET_VAR, \true);
            }
            return null;
        }
        if ($node instanceof Attribute) {
            ++$this->attributeDepth;
            return null;
        }
        if ($node instanceof Array_) {
            $this->processArrayInSerializedCallableKey($node);
            return null;
        }
        if ($node instanceof MethodCall) {
            $this->processSetFactoryArgument($node);
            return null;
        }
        if ($node instanceof If_ || $node instanceof Else_ || $node instanceof ElseIf_) {
            $this->processContextInIf($node);
            return null;
        }
        if ($node instanceof Arg) {
            $node->value->setAttribute(AttributeKey::IS_ARG_VALUE, \true);
            if ($node->unpack) {
                $node->value->setAttribute(AttributeKey::IS_UNPACKED_ARG_VALUE, \true);
            }
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
    /**
     * @return null
     */
    public function leaveNode(Node $node)
    {
        if ($node instanceof For_ || $node instanceof Foreach_ || $node instanceof While_ || $node instanceof Do_ || $node instanceof Switch_) {
            --$this->loopDepth;
            return null;
        }
        if ($node instanceof TryCatch) {
            --$this->tryDepth;
            return null;
        }
        // back to the try stmts of the parent TryCatch, see decorateEnclosingContext()
        if ($node instanceof Catch_ || $node instanceof Finally_) {
            ++$this->tryDepth;
            return null;
        }
        if ($node instanceof Attribute) {
            --$this->attributeDepth;
            return null;
        }
        if ($node instanceof Class_ || $node instanceof FunctionLike) {
            [$this->loopDepth, $this->tryDepth] = array_pop($this->outerLoopAndTryDepths) ?? [0, 0];
        }
        return null;
    }
    private function decorateEnclosingContext(Node $node): void
    {
        // catch and finally are not part of the try block of their parent TryCatch
        if ($node instanceof Catch_ || $node instanceof Finally_) {
            --$this->tryDepth;
        }
        // nested class or function-like does not belong to outer loop or try block
        if ($node instanceof Class_ || $node instanceof FunctionLike) {
            $this->outerLoopAndTryDepths[] = [$this->loopDepth, $this->tryDepth];
            $this->loopDepth = 0;
            $this->tryDepth = 0;
        } elseif ($this->tryDepth > 0) {
            $node->setAttribute(AttributeKey::IS_IN_TRY_BLOCK, \true);
        }
        if ($this->loopDepth > 0 && ($node instanceof If_ || $node instanceof Break_)) {
            $node->setAttribute(AttributeKey::IS_IN_LOOP_OR_SWITCH, \true);
        }
        if ($this->attributeDepth > 0) {
            if ($node instanceof Array_) {
                $node->setAttribute(AttributeKey::IS_ARRAY_IN_ATTRIBUTE, \true);
            }
            if ($node instanceof Closure) {
                $node->setAttribute(AttributeKey::IS_CLOSURE_IN_ATTRIBUTE, \true);
            }
        }
    }
    private function processInsideArrayDimFetch(ArrayDimFetch $arrayDimFetch): void
    {
        if ($arrayDimFetch->var instanceof PropertyFetch || $arrayDimFetch->var instanceof StaticPropertyFetch) {
            $arrayDimFetch->var->setAttribute(AttributeKey::INSIDE_ARRAY_DIM_FETCH, \true);
        }
    }
    private function processContextInClass(Node $node): void
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
    /**
     * The array is data, not a callable to convert: 'callback'/'factory' keyed item holds
     * a [$value, 'method'] pair likely to be serialized as a string.
     */
    private function processArrayInSerializedCallableKey(Array_ $array): void
    {
        foreach ($array->items as $arrayItem) {
            if (!$arrayItem instanceof ArrayItem) {
                continue;
            }
            if (!$arrayItem->key instanceof String_) {
                continue;
            }
            if (!in_array($arrayItem->key->value, ['callback', 'factory', 'validation_groups'], \true)) {
                continue;
            }
            if ($arrayItem->value instanceof Array_) {
                $arrayItem->value->setAttribute(AttributeKey::IS_ARRAY_AS_STRING_CALLABLE, \true);
            }
        }
    }
    /**
     * Symfony's Definition::setFactory() does not accept a first class callable,
     * keep the [$value, 'method'] array as is.
     */
    private function processSetFactoryArgument(MethodCall $methodCall): void
    {
        if (!$methodCall->name instanceof Identifier) {
            return;
        }
        if ($methodCall->name->toString() !== 'setFactory') {
            return;
        }
        $firstArg = $methodCall->args[0] ?? null;
        if (!$firstArg instanceof Arg) {
            return;
        }
        if ($firstArg->value instanceof Array_) {
            $firstArg->value->setAttribute(AttributeKey::IS_ARRAY_AS_STRING_CALLABLE, \true);
        }
    }
    /**
     * @param \PhpParser\Node\Stmt\Else_|\PhpParser\Node\Stmt\ElseIf_|\PhpParser\Node\Stmt\If_ $node
     */
    private function processContextInIf($node): void
    {
        foreach ($node->stmts as $stmt) {
            if ($stmt instanceof Break_) {
                $stmt->setAttribute(AttributeKey::IS_IN_IF, \true);
            }
        }
    }
    /**
     * @param \PhpParser\Node\Stmt\Do_|\PhpParser\Node\Stmt\For_|\PhpParser\Node\Stmt\Foreach_|\PhpParser\Node\Stmt\Switch_|\PhpParser\Node\Stmt\While_ $node
     */
    private function processContextInLoop($node): void
    {
        if ($node instanceof Foreach_) {
            if ($node->keyVar instanceof Variable) {
                $node->keyVar->setAttribute(AttributeKey::IS_VARIABLE_LOOP, \true);
            }
            $node->valueVar->setAttribute(AttributeKey::IS_VARIABLE_LOOP, \true);
        }
    }
}
