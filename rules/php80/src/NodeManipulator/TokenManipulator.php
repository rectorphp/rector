<?php

declare(strict_types=1);

namespace Rector\Php80\NodeManipulator;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Stmt\If_;
use PHPStan\Type\ArrayType;
use Rector\Core\PhpParser\Node\Value\ValueResolver;
use Rector\Core\PhpParser\NodeTraverser\CallableNodeTraverser;
use Rector\Core\PhpParser\Printer\BetterStandardPrinter;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\PostRector\Collector\NodesToRemoveCollector;

final class TokenManipulator
{
    /**
     * @var CallableNodeTraverser
     */
    private $callableNodeTraverser;

    /**
     * @var ValueResolver
     */
    private $valueResolver;

    /**
     * @var BetterStandardPrinter
     */
    private $betterStandardPrinter;

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var Expr|null
     */
    private $assignedNameExpr;

    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    /**
     * @var NodesToRemoveCollector
     */
    private $nodesToRemoveCollector;

    public function __construct(
        CallableNodeTraverser $callableNodeTraverser,
        ValueResolver $valueResolver,
        BetterStandardPrinter $betterStandardPrinter,
        NodeNameResolver $nodeNameResolver,
        NodeTypeResolver $nodeTypeResolver,
        NodesToRemoveCollector $nodesToRemoveCollector
    ) {
        $this->callableNodeTraverser = $callableNodeTraverser;
        $this->valueResolver = $valueResolver;
        $this->betterStandardPrinter = $betterStandardPrinter;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->nodesToRemoveCollector = $nodesToRemoveCollector;
    }

    /**
     * @param Node[] $nodes
     */
    public function refactorArrayToken(array $nodes, Expr $singleTokenExpr): void
    {
        $this->replaceTokenDimFetchZeroWithGetTokenName($nodes, $singleTokenExpr);

        // replace "$token[1]"; with "$token->value"
        $this->callableNodeTraverser->traverseNodesWithCallable($nodes, function (Node $node) {
            if (! $this->isArrayDimFetchWithDimIntegerValue($node, 1)) {
                return null;
            }

            /** @var ArrayDimFetch $node */
            $tokenStaticType = $this->nodeTypeResolver->getStaticType($node->var);
            if (! $tokenStaticType instanceof ArrayType) {
                return null;
            }

            return new PropertyFetch($node->var, 'text');
        });
    }

    /**
     * @param Node[] $nodes
     */
    public function refactorNonArrayToken(array $nodes, Expr $singleTokenExpr): void
    {
        // replace "$content = $token;" → "$content = $token->text;"
        $this->callableNodeTraverser->traverseNodesWithCallable($nodes, function (Node $node) use ($singleTokenExpr) {
            if (! $node instanceof Assign) {
                return null;
            }

            if (! $this->betterStandardPrinter->areNodesEqual($node->expr, $singleTokenExpr)) {
                return null;
            }

            $tokenStaticType = $this->nodeTypeResolver->getStaticType($node->expr);
            if ($tokenStaticType instanceof ArrayType) {
                return null;
            }

            $node->expr = new PropertyFetch($singleTokenExpr, 'text');
        });

        // replace "$name = null;" → "$name = $token->getTokenName();"
        $this->callableNodeTraverser->traverseNodesWithCallable($nodes, function (Node $node) use ($singleTokenExpr) {
            if (! $node instanceof Assign) {
                return null;
            }

            $tokenStaticType = $this->nodeTypeResolver->getStaticType($node->expr);
            if ($tokenStaticType instanceof ArrayType) {
                return null;
            }

            if ($this->assignedNameExpr === null) {
                return null;
            }

            if (! $this->betterStandardPrinter->areNodesEqual($node->var, $this->assignedNameExpr)) {
                return null;
            }

            if (! $this->valueResolver->isValue($node->expr, 'null')) {
                return null;
            }

            $node->expr = new MethodCall($singleTokenExpr, 'getTokenName');

            return $node;
        });
    }

    public function refactorTokenIsKind(array $nodes, Expr $singleTokenExpr): void
    {
        $this->callableNodeTraverser->traverseNodesWithCallable($nodes, function (Node $node) use ($singleTokenExpr) {
            if (! $node instanceof Identical) {
                return null;
            }

            $tokenArrayDimFetchAndTConstantType = $this->matchTokenArrayDimFetchAndTConstantType($node);
            if ($tokenArrayDimFetchAndTConstantType === null) {
                return null;
            }

            [$arrayDimFetch, $constFetch] = $tokenArrayDimFetchAndTConstantType;

            /** @var ArrayDimFetch $arrayDimFetch */
            if (! $this->isArrayDimFetchWithDimIntegerValue($arrayDimFetch, 0)) {
                return null;
            }

            if (! $this->betterStandardPrinter->areNodesEqual($arrayDimFetch->var, $singleTokenExpr)) {
                return null;
            }

            /** @var ConstFetch $constFetch */
            $constName = $this->nodeNameResolver->getName($constFetch);
            if ($constName === null) {
                return null;
            }

            if (! Strings::match($constName, '#^T_#')) {
                return null;
            }

            return $this->createIsTConstTypeMethodCall($arrayDimFetch, $constFetch);
        });
    }

    /**
     * @param Node[] $nodes
     */
    public function removeIsArray(array $nodes, Expr\Variable $singleTokenVariable): void
    {
        $this->callableNodeTraverser->traverseNodesWithCallable($nodes, function (Node $node) use (
            $singleTokenVariable
        ) {
            if (! $node instanceof FuncCall) {
                return null;
            }

            if (! $this->nodeNameResolver->isName($node, 'is_array')) {
                return null;
            }

            if (! $this->betterStandardPrinter->areNodesEqual($node->args[0]->value, $singleTokenVariable)) {
                return null;
            }

            if ($this->shouldSkipNodeRemovalForPartOfIf($node)) {
                return null;
            }

            // remove correct node
            $nodeToRemove = $this->matchParentNodeInCaseOfIdenticalTrue($node);

            $this->nodesToRemoveCollector->addNodeToRemove($nodeToRemove);
        });
    }

    /**
     * Replace $token[0] with $token->getTokenName() call
     *
     * @param Node[] $nodes
     */
    private function replaceTokenDimFetchZeroWithGetTokenName(array $nodes, Expr $singleTokenExpr): void
    {
        $this->callableNodeTraverser->traverseNodesWithCallable($nodes, function (Node $node) use ($singleTokenExpr) {
            if (! $node instanceof FuncCall) {
                return null;
            }

            if (! $this->nodeNameResolver->isName($node, 'token_name')) {
                return null;
            }

            $possibleTokenArray = $node->args[0]->value;
            if (! $possibleTokenArray instanceof ArrayDimFetch) {
                return null;
            }

            $tokenStaticType = $this->nodeTypeResolver->getStaticType($possibleTokenArray->var);
            if (! $tokenStaticType instanceof ArrayType) {
                return null;
            }

            if ($possibleTokenArray->dim === null) {
                return null;
            }

            if (! $this->valueResolver->isValue($possibleTokenArray->dim, 0)) {
                return null;
            }

            /** @var FuncCall $node */
            if (! $this->betterStandardPrinter->areNodesEqual($possibleTokenArray->var, $singleTokenExpr)) {
                return null;
            }

            // save token variable name for later
            $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
            if ($parentNode instanceof Assign) {
                $this->assignedNameExpr = $parentNode->var;
            }

            return new MethodCall($singleTokenExpr, 'getTokenName');
        });
    }

    private function isArrayDimFetchWithDimIntegerValue(Node $node, int $value): bool
    {
        if (! $node instanceof ArrayDimFetch) {
            return false;
        }

        if ($node->dim === null) {
            return false;
        }

        return $this->valueResolver->isValue($node->dim, $value);
    }

    private function createIsTConstTypeMethodCall(ArrayDimFetch $arrayDimFetch, ConstFetch $constFetch): MethodCall
    {
        return new MethodCall($arrayDimFetch->var, 'is', [new Arg($constFetch)]);
    }

    private function shouldSkipNodeRemovalForPartOfIf(FuncCall $funcCall): bool
    {
        $parentNode = $funcCall->getAttribute(AttributeKey::PARENT_NODE);

        // cannot remove x from if(x)
        if ($parentNode instanceof If_ && $parentNode->cond === $funcCall) {
            return true;
        }

        if ($parentNode instanceof Expr\BooleanNot) {
            $parentParentNode = $parentNode->getAttribute(AttributeKey::PARENT_NODE);
            if ($parentParentNode instanceof If_) {
                $parentParentNode->cond = $parentNode;
                return true;
            }
        }

        return false;
    }

    /**
     * @return ArrayDimFetch[]|ConstFetch[]|null
     */
    private function matchTokenArrayDimFetchAndTConstantType(Identical $identical): ?array
    {
        if ($identical->left instanceof ArrayDimFetch && $identical->right instanceof ConstFetch) {
            return [$identical->left, $identical->right];
        }

        if ($identical->right instanceof ArrayDimFetch && $identical->left instanceof ConstFetch) {
            return [$identical->right, $identical->left];
        }

        return null;
    }

    private function matchParentNodeInCaseOfIdenticalTrue(FuncCall $funcCall): Node
    {
        $parentNode = $funcCall->getAttribute(AttributeKey::PARENT_NODE);
        if ($parentNode instanceof Identical) {
            if ($parentNode->left === $funcCall && $this->isTrueConstant($parentNode->right)) {
                return $parentNode;
            }

            if ($parentNode->right === $funcCall && $this->isTrueConstant($parentNode->left)) {
                return $parentNode;
            }
        }

        return $funcCall;
    }

    private function isTrueConstant(Expr $expr): bool
    {
        if (! $expr instanceof ConstFetch) {
            return false;
        }

        return $expr->name->toLowerString() === 'true';
    }
}
