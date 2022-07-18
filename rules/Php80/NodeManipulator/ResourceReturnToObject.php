<?php

declare (strict_types=1);
namespace Rector\Php80\NodeManipulator;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Name\FullyQualified;
use PHPStan\Type\Type;
use Rector\Core\PhpParser\Comparing\NodeComparator;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
final class ResourceReturnToObject
{
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
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Comparing\NodeComparator
     */
    private $nodeComparator;
    public function __construct(NodeNameResolver $nodeNameResolver, NodeTypeResolver $nodeTypeResolver, BetterNodeFinder $betterNodeFinder, NodeComparator $nodeComparator)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeComparator = $nodeComparator;
    }
    /**
     * @param array<string, string> $collectionFunctionToReturnObject
     * @param \PhpParser\Node\Expr\FuncCall|\PhpParser\Node\Expr\BinaryOp\BooleanOr $node
     * @return \PhpParser\Node\Expr\FuncCall|\PhpParser\Node\Expr\BinaryOp\BooleanOr|\PhpParser\Node\Expr\Instanceof_|null
     */
    public function refactor($node, array $collectionFunctionToReturnObject)
    {
        if ($node instanceof FuncCall) {
            return $this->processFuncCall($node, $collectionFunctionToReturnObject);
        }
        return $this->processBooleanOr($node, $collectionFunctionToReturnObject);
    }
    /**
     * @param array<string, string> $collectionFunctionToReturnObject
     */
    private function processFuncCall(FuncCall $funcCall, array $collectionFunctionToReturnObject) : ?Instanceof_
    {
        $parent = $funcCall->getAttribute(AttributeKey::PARENT_NODE);
        if ($parent instanceof BinaryOp && !$parent instanceof BooleanOr) {
            return null;
        }
        if ($this->shouldSkip($funcCall)) {
            return null;
        }
        $objectInstanceCheck = $this->resolveObjectInstanceCheck($funcCall, $collectionFunctionToReturnObject);
        if ($objectInstanceCheck === null) {
            return null;
        }
        /** @var Expr $argResourceValue */
        $argResourceValue = $funcCall->args[0]->value;
        return new Instanceof_($argResourceValue, new FullyQualified($objectInstanceCheck));
    }
    /**
     * @param array<string, string> $collectionFunctionToReturnObject
     */
    private function resolveArgValueType(FuncCall $funcCall, array $collectionFunctionToReturnObject) : ?Type
    {
        /** @var Expr $argResourceValue */
        $argResourceValue = $funcCall->args[0]->value;
        $argValueType = $this->nodeTypeResolver->getType($argResourceValue);
        // if detected type is not FullyQualifiedObjectType, it still can be a resource to object, when:
        //      - in the right position of BooleanOr, it be NeverType
        //      - the object changed after init
        if (!$argValueType instanceof FullyQualifiedObjectType) {
            return $this->resolveArgValueTypeFromPreviousAssign($funcCall, $argResourceValue, $collectionFunctionToReturnObject);
        }
        return $argValueType;
    }
    /**
     * @param array<string, string> $collectionFunctionToReturnObject
     */
    private function resolveObjectInstanceCheck(FuncCall $funcCall, array $collectionFunctionToReturnObject) : ?string
    {
        $argValueType = $this->resolveArgValueType($funcCall, $collectionFunctionToReturnObject);
        if (!$argValueType instanceof FullyQualifiedObjectType) {
            return null;
        }
        $className = $argValueType->getClassName();
        foreach ($collectionFunctionToReturnObject as $singleCollectionFunctionToReturnObject) {
            if ($className === $singleCollectionFunctionToReturnObject) {
                return $singleCollectionFunctionToReturnObject;
            }
        }
        return null;
    }
    /**
     * @param array<string, string> $collectionFunctionToReturnObject
     */
    private function resolveArgValueTypeFromPreviousAssign(FuncCall $funcCall, Expr $expr, array $collectionFunctionToReturnObject) : ?FullyQualifiedObjectType
    {
        $objectInstanceCheck = null;
        $foundNode = $this->betterNodeFinder->findFirstPrevious($funcCall, function (Node $subNode) use(&$objectInstanceCheck, $expr, $collectionFunctionToReturnObject) : bool {
            if (!$this->isAssignWithFuncCallExpr($subNode)) {
                return \false;
            }
            /** @var Assign $subNode */
            if (!$this->nodeComparator->areNodesEqual($subNode->var, $expr)) {
                return \false;
            }
            foreach ($collectionFunctionToReturnObject as $key => $value) {
                if ($this->nodeNameResolver->isName($subNode->expr, $key)) {
                    $objectInstanceCheck = $value;
                    return \true;
                }
            }
            return \false;
        });
        if (!$foundNode instanceof Assign) {
            return null;
        }
        /** @var string $objectInstanceCheck */
        return new FullyQualifiedObjectType($objectInstanceCheck);
    }
    private function isAssignWithFuncCallExpr(Node $node) : bool
    {
        if (!$node instanceof Assign) {
            return \false;
        }
        return $node->expr instanceof FuncCall;
    }
    /**
     * @param array<string, string> $collectionFunctionToReturnObject
     */
    private function processBooleanOr(BooleanOr $booleanOr, array $collectionFunctionToReturnObject) : ?Instanceof_
    {
        $left = $booleanOr->left;
        $right = $booleanOr->right;
        $funCall = null;
        $instanceof = null;
        if ($left instanceof FuncCall && $right instanceof Instanceof_) {
            $funCall = $left;
            $instanceof = $right;
        } elseif ($left instanceof Instanceof_ && $right instanceof FuncCall) {
            $funCall = $right;
            $instanceof = $left;
        } else {
            return null;
        }
        /** @var FuncCall $funCall */
        if ($this->shouldSkip($funCall)) {
            return null;
        }
        $objectInstanceCheck = $this->resolveObjectInstanceCheck($funCall, $collectionFunctionToReturnObject);
        if ($objectInstanceCheck === null) {
            return null;
        }
        /** @var Expr $argResourceValue */
        $argResourceValue = $funCall->args[0]->value;
        /** @var Instanceof_ $instanceof */
        if (!$this->isInstanceOfObjectCheck($instanceof, $argResourceValue, $objectInstanceCheck)) {
            return null;
        }
        return $instanceof;
    }
    private function isInstanceOfObjectCheck(Instanceof_ $instanceof, Expr $expr, string $objectInstanceCheck) : bool
    {
        if (!$instanceof->class instanceof FullyQualified) {
            return \false;
        }
        if (!$this->nodeComparator->areNodesEqual($expr, $instanceof->expr)) {
            return \false;
        }
        return $this->nodeNameResolver->isName($instanceof->class, $objectInstanceCheck);
    }
    private function shouldSkip(FuncCall $funcCall) : bool
    {
        if (!$this->nodeNameResolver->isName($funcCall, 'is_resource')) {
            return \true;
        }
        if (!isset($funcCall->args[0])) {
            return \true;
        }
        $argResource = $funcCall->args[0];
        return !$argResource instanceof Arg;
    }
}
