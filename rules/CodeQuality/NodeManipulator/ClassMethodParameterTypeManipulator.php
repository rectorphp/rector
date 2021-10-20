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
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\NodeAnalyzer\ParamAnalyzer;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;
use RectorPrefix20211020\Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;
final class ClassMethodParameterTypeManipulator
{
    /**
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    /**
     * @var \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger
     */
    private $phpDocTypeChanger;
    /**
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    /**
     * @var \Rector\Core\NodeAnalyzer\ParamAnalyzer
     */
    private $paramAnalyzer;
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @var \Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser
     */
    private $simpleCallableNodeTraverser;
    public function __construct(\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory $phpDocInfoFactory, \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger $phpDocTypeChanger, \Rector\NodeTypeResolver\NodeTypeResolver $nodeTypeResolver, \Rector\Core\NodeAnalyzer\ParamAnalyzer $paramAnalyzer, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \RectorPrefix20211020\Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser $simpleCallableNodeTraverser)
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
    public function refactorFunctionParameters(\PhpParser\Node\Stmt\ClassMethod $classMethod, \PHPStan\Type\ObjectType $objectType, $replaceIntoType, \PHPStan\Type\Type $phpDocType, array $methodsReturningClassInstance) : void
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
    private function refactorParamTypeHint(\PhpParser\Node\Param $param, $replaceIntoType) : void
    {
        if ($this->paramAnalyzer->isNullable($param) && !$replaceIntoType instanceof \PhpParser\Node\NullableType) {
            $replaceIntoType = new \PhpParser\Node\NullableType($replaceIntoType);
        }
        $param->type = $replaceIntoType;
    }
    private function refactorParamDocBlock(\PhpParser\Node\Param $param, \PhpParser\Node\Stmt\ClassMethod $classMethod, \PHPStan\Type\Type $phpDocType) : void
    {
        $paramName = $this->nodeNameResolver->getName($param->var);
        if ($paramName === null) {
            throw new \Rector\Core\Exception\ShouldNotHappenException();
        }
        if ($this->paramAnalyzer->isNullable($param)) {
            if ($phpDocType instanceof \PHPStan\Type\UnionType) {
                $item0Unpacked = $phpDocType->getTypes();
                // Adding a UnionType into a new UnionType throws an exception so we need to "unpack" the types
                $phpDocType = new \PHPStan\Type\UnionType(\array_merge($item0Unpacked, [new \PHPStan\Type\NullType()]));
            } else {
                $phpDocType = new \PHPStan\Type\UnionType([$phpDocType, new \PHPStan\Type\NullType()]);
            }
        }
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);
        $this->phpDocTypeChanger->changeParamType($phpDocInfo, $phpDocType, $param, $paramName);
    }
    /**
     * @param string[] $methodsReturningClassInstance
     */
    private function refactorMethodCalls(\PhpParser\Node\Param $param, \PhpParser\Node\Stmt\ClassMethod $classMethod, array $methodsReturningClassInstance) : void
    {
        if ($classMethod->stmts === null) {
            return;
        }
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($classMethod->stmts, function (\PhpParser\Node $node) use($param, $methodsReturningClassInstance) : void {
            if (!$node instanceof \PhpParser\Node\Expr\MethodCall) {
                return;
            }
            $this->refactorMethodCall($param, $node, $methodsReturningClassInstance);
        });
    }
    /**
     * @param string[] $methodsReturningClassInstance
     */
    private function refactorMethodCall(\PhpParser\Node\Param $param, \PhpParser\Node\Expr\MethodCall $methodCall, array $methodsReturningClassInstance) : void
    {
        $paramName = $this->nodeNameResolver->getName($param->var);
        if ($paramName === null) {
            return;
        }
        if ($this->shouldSkipMethodCallRefactor($paramName, $methodCall, $methodsReturningClassInstance)) {
            return;
        }
        $assign = new \PhpParser\Node\Expr\Assign(new \PhpParser\Node\Expr\Variable($paramName), $methodCall);
        /** @var Node $parent */
        $parent = $methodCall->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if ($parent instanceof \PhpParser\Node\Arg) {
            $parent->value = $assign;
            return;
        }
        if (!$parent instanceof \PhpParser\Node\Stmt\Expression) {
            return;
        }
        $parent->expr = $assign;
    }
    /**
     * @param string[] $methodsReturningClassInstance
     */
    private function shouldSkipMethodCallRefactor(string $paramName, \PhpParser\Node\Expr\MethodCall $methodCall, array $methodsReturningClassInstance) : bool
    {
        if (!$this->nodeNameResolver->isName($methodCall->var, $paramName)) {
            return \true;
        }
        if (!$this->nodeNameResolver->isNames($methodCall->name, $methodsReturningClassInstance)) {
            return \true;
        }
        $parentNode = $methodCall->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if (!$parentNode instanceof \PhpParser\Node) {
            return \true;
        }
        return $parentNode instanceof \PhpParser\Node\Expr\Assign;
    }
}
