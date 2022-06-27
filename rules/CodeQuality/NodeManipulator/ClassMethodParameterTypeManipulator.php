<?php

declare (strict_types=1);
namespace Rector\CodeQuality\NodeManipulator;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\NodeAnalyzer\ParamAnalyzer;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\NodeTypeResolver\PHPStan\Type\UnionTypeFactory;
use RectorPrefix202206\Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;
final class ClassMethodParameterTypeManipulator
{
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger
     */
    private $phpDocTypeChanger;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    /**
     * @readonly
     * @var \Rector\Core\NodeAnalyzer\ParamAnalyzer
     */
    private $paramAnalyzer;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser
     */
    private $simpleCallableNodeTraverser;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\PHPStan\Type\UnionTypeFactory
     */
    private $unionTypeFactory;
    public function __construct(PhpDocInfoFactory $phpDocInfoFactory, PhpDocTypeChanger $phpDocTypeChanger, NodeTypeResolver $nodeTypeResolver, ParamAnalyzer $paramAnalyzer, NodeNameResolver $nodeNameResolver, SimpleCallableNodeTraverser $simpleCallableNodeTraverser, UnionTypeFactory $unionTypeFactory)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->phpDocTypeChanger = $phpDocTypeChanger;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->paramAnalyzer = $paramAnalyzer;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
        $this->unionTypeFactory = $unionTypeFactory;
    }
    /**
     * @param string[] $methodsReturningClassInstance
     * @param \PhpParser\Node\Identifier|\PhpParser\Node\Name|\PhpParser\Node\NullableType $replaceIntoType
     */
    public function refactorFunctionParameters(ClassMethod $classMethod, ObjectType $objectType, $replaceIntoType, Type $phpDocType, array $methodsReturningClassInstance) : void
    {
        foreach ($classMethod->getParams() as $param) {
            if (!$this->nodeTypeResolver->isObjectType($param, $objectType)) {
                continue;
            }
            $paramType = $this->nodeTypeResolver->getType($param);
            if (!$paramType->isSuperTypeOf($objectType)->yes()) {
                continue;
            }
            $this->refactorParamTypeHint($param, $replaceIntoType);
            $this->refactorParamDocBlock($param, $classMethod, $phpDocType);
            $this->refactorMethodCalls($param, $classMethod, $methodsReturningClassInstance);
        }
    }
    /**
     * @param \PhpParser\Node\Identifier|\PhpParser\Node\Name|\PhpParser\Node\NullableType $replaceIntoType
     */
    private function refactorParamTypeHint(Param $param, $replaceIntoType) : void
    {
        if ($this->paramAnalyzer->isNullable($param) && !$replaceIntoType instanceof NullableType) {
            $param->type = new NullableType($replaceIntoType);
        } else {
            $param->type = $replaceIntoType;
        }
    }
    private function refactorParamDocBlock(Param $param, ClassMethod $classMethod, Type $phpDocType) : void
    {
        $paramName = $this->nodeNameResolver->getName($param->var);
        if ($paramName === null) {
            throw new ShouldNotHappenException();
        }
        if ($this->paramAnalyzer->isNullable($param)) {
            $phpDocType = $this->unionTypeFactory->createNullableUnionType($phpDocType);
        }
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);
        $this->phpDocTypeChanger->changeParamType($phpDocInfo, $phpDocType, $param, $paramName);
    }
    /**
     * @param string[] $methodsReturningClassInstance
     */
    private function refactorMethodCalls(Param $param, ClassMethod $classMethod, array $methodsReturningClassInstance) : void
    {
        if ($classMethod->stmts === null) {
            return;
        }
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($classMethod->stmts, function (Node $node) use($param, $methodsReturningClassInstance) {
            if (!$node instanceof MethodCall) {
                return null;
            }
            $this->refactorMethodCall($param, $node, $methodsReturningClassInstance);
            return null;
        });
    }
    /**
     * @param string[] $methodsReturningClassInstance
     */
    private function refactorMethodCall(Param $param, MethodCall $methodCall, array $methodsReturningClassInstance) : void
    {
        $paramName = $this->nodeNameResolver->getName($param->var);
        if ($paramName === null) {
            return;
        }
        if ($this->shouldSkipMethodCallRefactor($paramName, $methodCall, $methodsReturningClassInstance)) {
            return;
        }
        $assign = new Assign(new Variable($paramName), $methodCall);
        /** @var Node $parent */
        $parent = $methodCall->getAttribute(AttributeKey::PARENT_NODE);
        if ($parent instanceof Arg) {
            $parent->value = $assign;
            return;
        }
        if (!$parent instanceof Expression) {
            return;
        }
        $parent->expr = $assign;
    }
    /**
     * @param string[] $methodsReturningClassInstance
     */
    private function shouldSkipMethodCallRefactor(string $paramName, MethodCall $methodCall, array $methodsReturningClassInstance) : bool
    {
        if (!$this->nodeNameResolver->isName($methodCall->var, $paramName)) {
            return \true;
        }
        if (!$this->nodeNameResolver->isNames($methodCall->name, $methodsReturningClassInstance)) {
            return \true;
        }
        $parentNode = $methodCall->getAttribute(AttributeKey::PARENT_NODE);
        if (!$parentNode instanceof Node) {
            return \true;
        }
        return $parentNode instanceof Assign;
    }
}
