<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\CodeQuality\NodeManipulator;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Arg;
use RectorPrefix20220606\PhpParser\Node\Expr\Assign;
use RectorPrefix20220606\PhpParser\Node\Expr\MethodCall;
use RectorPrefix20220606\PhpParser\Node\Expr\Variable;
use RectorPrefix20220606\PhpParser\Node\Identifier;
use RectorPrefix20220606\PhpParser\Node\Name;
use RectorPrefix20220606\PhpParser\Node\NullableType;
use RectorPrefix20220606\PhpParser\Node\Param;
use RectorPrefix20220606\PhpParser\Node\Stmt\ClassMethod;
use RectorPrefix20220606\PhpParser\Node\Stmt\Expression;
use RectorPrefix20220606\PHPStan\Type\NullType;
use RectorPrefix20220606\PHPStan\Type\ObjectType;
use RectorPrefix20220606\PHPStan\Type\Type;
use RectorPrefix20220606\PHPStan\Type\UnionType;
use RectorPrefix20220606\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use RectorPrefix20220606\Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use RectorPrefix20220606\Rector\Core\Exception\ShouldNotHappenException;
use RectorPrefix20220606\Rector\Core\NodeAnalyzer\ParamAnalyzer;
use RectorPrefix20220606\Rector\NodeNameResolver\NodeNameResolver;
use RectorPrefix20220606\Rector\NodeTypeResolver\Node\AttributeKey;
use RectorPrefix20220606\Rector\NodeTypeResolver\NodeTypeResolver;
use RectorPrefix20220606\Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;
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
    public function __construct(PhpDocInfoFactory $phpDocInfoFactory, PhpDocTypeChanger $phpDocTypeChanger, NodeTypeResolver $nodeTypeResolver, ParamAnalyzer $paramAnalyzer, NodeNameResolver $nodeNameResolver, SimpleCallableNodeTraverser $simpleCallableNodeTraverser)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->phpDocTypeChanger = $phpDocTypeChanger;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->paramAnalyzer = $paramAnalyzer;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
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
            $replaceIntoType = new NullableType($replaceIntoType);
        }
        $param->type = $replaceIntoType;
    }
    private function refactorParamDocBlock(Param $param, ClassMethod $classMethod, Type $phpDocType) : void
    {
        $paramName = $this->nodeNameResolver->getName($param->var);
        if ($paramName === null) {
            throw new ShouldNotHappenException();
        }
        if ($this->paramAnalyzer->isNullable($param)) {
            if ($phpDocType instanceof UnionType) {
                $item0Unpacked = $phpDocType->getTypes();
                // Adding a UnionType into a new UnionType throws an exception so we need to "unpack" the types
                $phpDocType = new UnionType(\array_merge($item0Unpacked, [new NullType()]));
            } else {
                $phpDocType = new UnionType([$phpDocType, new NullType()]);
            }
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
