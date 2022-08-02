<?php

declare (strict_types=1);
namespace Rector\Php80\NodeManipulator;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\If_;
use PHPStan\Type\ArrayType;
use Rector\Core\NodeAnalyzer\ArgsAnalyzer;
use Rector\Core\PhpParser\Comparing\NodeComparator;
use Rector\Core\PhpParser\Node\Value\ValueResolver;
use Rector\Core\Util\StringUtils;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\Php80\ValueObject\ArrayDimFetchAndConstFetch;
use Rector\PostRector\Collector\NodesToRemoveCollector;
use RectorPrefix202208\Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;
final class TokenManipulator
{
    /**
     * @var \PhpParser\Node\Expr|null
     */
    private $assignedNameExpr;
    /**
     * @readonly
     * @var \Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser
     */
    private $simpleCallableNodeTraverser;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    /**
     * @readonly
     * @var \Rector\PostRector\Collector\NodesToRemoveCollector
     */
    private $nodesToRemoveCollector;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\Value\ValueResolver
     */
    private $valueResolver;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Comparing\NodeComparator
     */
    private $nodeComparator;
    /**
     * @readonly
     * @var \Rector\Core\NodeAnalyzer\ArgsAnalyzer
     */
    private $argsAnalyzer;
    public function __construct(SimpleCallableNodeTraverser $simpleCallableNodeTraverser, NodeNameResolver $nodeNameResolver, NodeTypeResolver $nodeTypeResolver, NodesToRemoveCollector $nodesToRemoveCollector, ValueResolver $valueResolver, NodeComparator $nodeComparator, ArgsAnalyzer $argsAnalyzer)
    {
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->nodesToRemoveCollector = $nodesToRemoveCollector;
        $this->valueResolver = $valueResolver;
        $this->nodeComparator = $nodeComparator;
        $this->argsAnalyzer = $argsAnalyzer;
    }
    /**
     * @param Node[] $nodes
     */
    public function refactorArrayToken(array $nodes, Variable $singleTokenVariable) : void
    {
        $this->replaceTokenDimFetchZeroWithGetTokenName($nodes, $singleTokenVariable);
        // replace "$token[1]"; with "$token->value"
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($nodes, function (Node $node) : ?PropertyFetch {
            if (!$node instanceof ArrayDimFetch) {
                return null;
            }
            if (!$this->isArrayDimFetchWithDimIntegerValue($node, 1)) {
                return null;
            }
            $tokenStaticType = $this->nodeTypeResolver->getType($node->var);
            if (!$tokenStaticType instanceof ArrayType) {
                return null;
            }
            return new PropertyFetch($node->var, 'text');
        });
    }
    /**
     * @param Node[] $nodes
     */
    public function refactorNonArrayToken(array $nodes, Variable $singleTokenVariable) : void
    {
        // replace "$content = $token;" → "$content = $token->text;"
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($nodes, function (Node $node) use($singleTokenVariable) {
            if (!$node instanceof Assign) {
                return null;
            }
            if (!$this->nodeComparator->areNodesEqual($node->expr, $singleTokenVariable)) {
                return null;
            }
            $tokenStaticType = $this->nodeTypeResolver->getType($node->expr);
            if ($tokenStaticType instanceof ArrayType) {
                return null;
            }
            $node->expr = new PropertyFetch($singleTokenVariable, 'text');
        });
        // replace "$name = null;" → "$name = $token->getTokenName();"
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($nodes, function (Node $node) use($singleTokenVariable) : ?Assign {
            if (!$node instanceof Assign) {
                return null;
            }
            $tokenStaticType = $this->nodeTypeResolver->getType($node->expr);
            if ($tokenStaticType instanceof ArrayType) {
                return null;
            }
            if ($this->assignedNameExpr === null) {
                return null;
            }
            if (!$this->nodeComparator->areNodesEqual($node->var, $this->assignedNameExpr)) {
                return null;
            }
            if (!$this->valueResolver->isValue($node->expr, 'null')) {
                return null;
            }
            $node->expr = new MethodCall($singleTokenVariable, 'getTokenName');
            return $node;
        });
    }
    /**
     * @param Node[] $nodes
     */
    public function refactorTokenIsKind(array $nodes, Variable $singleTokenVariable) : void
    {
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($nodes, function (Node $node) use($singleTokenVariable) : ?MethodCall {
            if (!$node instanceof Identical) {
                return null;
            }
            $arrayDimFetchAndConstFetch = $this->matchArrayDimFetchAndConstFetch($node);
            if (!$arrayDimFetchAndConstFetch instanceof ArrayDimFetchAndConstFetch) {
                return null;
            }
            if (!$this->isArrayDimFetchWithDimIntegerValue($arrayDimFetchAndConstFetch->getArrayDimFetch(), 0)) {
                return null;
            }
            $arrayDimFetch = $arrayDimFetchAndConstFetch->getArrayDimFetch();
            $constFetch = $arrayDimFetchAndConstFetch->getConstFetch();
            if (!$this->nodeComparator->areNodesEqual($arrayDimFetch->var, $singleTokenVariable)) {
                return null;
            }
            $constName = $this->nodeNameResolver->getName($constFetch);
            if ($constName === null) {
                return null;
            }
            if (!StringUtils::isMatch($constName, '#^T_#')) {
                return null;
            }
            return $this->createIsTConstTypeMethodCall($arrayDimFetch, $arrayDimFetchAndConstFetch->getConstFetch());
        });
    }
    /**
     * @param Node[] $nodes
     */
    public function removeIsArray(array $nodes, Variable $singleTokenVariable) : void
    {
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($nodes, function (Node $node) use($singleTokenVariable) {
            if (!$node instanceof FuncCall) {
                return null;
            }
            if (!$this->nodeNameResolver->isName($node, 'is_array')) {
                return null;
            }
            if (!$this->argsAnalyzer->isArgInstanceInArgsPosition($node->args, 0)) {
                return null;
            }
            /** @var Arg $firstArg */
            $firstArg = $node->args[0];
            if (!$this->nodeComparator->areNodesEqual($firstArg->value, $singleTokenVariable)) {
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
    private function replaceTokenDimFetchZeroWithGetTokenName(array $nodes, Variable $singleTokenVariable) : void
    {
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($nodes, function (Node $node) use($singleTokenVariable) : ?MethodCall {
            if (!$node instanceof FuncCall) {
                return null;
            }
            if (!$this->nodeNameResolver->isName($node, 'token_name')) {
                return null;
            }
            if (!$this->argsAnalyzer->isArgInstanceInArgsPosition($node->args, 0)) {
                return null;
            }
            /** @var Arg $firstArg */
            $firstArg = $node->args[0];
            $possibleTokenArray = $firstArg->value;
            if (!$possibleTokenArray instanceof ArrayDimFetch) {
                return null;
            }
            $tokenStaticType = $this->nodeTypeResolver->getType($possibleTokenArray->var);
            if (!$tokenStaticType instanceof ArrayType) {
                return null;
            }
            if ($possibleTokenArray->dim === null) {
                return null;
            }
            if (!$this->valueResolver->isValue($possibleTokenArray->dim, 0)) {
                return null;
            }
            if (!$this->nodeComparator->areNodesEqual($possibleTokenArray->var, $singleTokenVariable)) {
                return null;
            }
            // save token variable name for later
            $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
            if ($parentNode instanceof Assign) {
                $this->assignedNameExpr = $parentNode->var;
            }
            return new MethodCall($singleTokenVariable, 'getTokenName');
        });
    }
    private function isArrayDimFetchWithDimIntegerValue(ArrayDimFetch $arrayDimFetch, int $value) : bool
    {
        if ($arrayDimFetch->dim === null) {
            return \false;
        }
        return $this->valueResolver->isValue($arrayDimFetch->dim, $value);
    }
    private function matchArrayDimFetchAndConstFetch(Identical $identical) : ?ArrayDimFetchAndConstFetch
    {
        if ($identical->left instanceof ArrayDimFetch && $identical->right instanceof ConstFetch) {
            return new ArrayDimFetchAndConstFetch($identical->left, $identical->right);
        }
        if (!$identical->right instanceof ArrayDimFetch) {
            return null;
        }
        if (!$identical->left instanceof ConstFetch) {
            return null;
        }
        return new ArrayDimFetchAndConstFetch($identical->right, $identical->left);
    }
    private function createIsTConstTypeMethodCall(ArrayDimFetch $arrayDimFetch, ConstFetch $constFetch) : MethodCall
    {
        return new MethodCall($arrayDimFetch->var, 'is', [new Arg($constFetch)]);
    }
    private function shouldSkipNodeRemovalForPartOfIf(FuncCall $funcCall) : bool
    {
        $parentNode = $funcCall->getAttribute(AttributeKey::PARENT_NODE);
        // cannot remove x from if(x)
        if ($parentNode instanceof If_ && $parentNode->cond === $funcCall) {
            return \true;
        }
        if ($parentNode instanceof BooleanNot) {
            $parentParentNode = $parentNode->getAttribute(AttributeKey::PARENT_NODE);
            if ($parentParentNode instanceof If_) {
                $parentParentNode->cond = $parentNode;
                return \true;
            }
        }
        return \false;
    }
    /**
     * @return \PhpParser\Node\Expr\BinaryOp\Identical|\PhpParser\Node\Expr\FuncCall
     */
    private function matchParentNodeInCaseOfIdenticalTrue(FuncCall $funcCall)
    {
        $parentNode = $funcCall->getAttribute(AttributeKey::PARENT_NODE);
        if ($parentNode instanceof Identical) {
            $isRightValueTrue = $this->valueResolver->isValue($parentNode->right, \true);
            if ($parentNode->left === $funcCall && $isRightValueTrue) {
                return $parentNode;
            }
            $isLeftValueTrue = $this->valueResolver->isValue($parentNode->left, \true);
            if ($parentNode->right === $funcCall && $isLeftValueTrue) {
                return $parentNode;
            }
        }
        return $funcCall;
    }
}
