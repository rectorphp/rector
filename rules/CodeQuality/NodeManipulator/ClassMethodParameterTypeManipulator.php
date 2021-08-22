<?php

declare(strict_types=1);

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
use Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;

final class ClassMethodParameterTypeManipulator
{
    public function __construct(
        private PhpDocInfoFactory $phpDocInfoFactory,
        private PhpDocTypeChanger $phpDocTypeChanger,
        private NodeTypeResolver $nodeTypeResolver,
        private ParamAnalyzer $paramAnalyzer,
        private NodeNameResolver $nodeNameResolver,
        private SimpleCallableNodeTraverser $simpleCallableNodeTraverser
    ) {
    }

    /**
     * @param string[] $methodsReturningClassInstance
     */
    public function refactorFunctionParameters(
        ClassMethod $classMethod,
        ObjectType $objectType,
        Identifier|Name|NullableType $replaceIntoType,
        Type $phpDocType,
        array $methodsReturningClassInstance
    ): void {
        foreach ($classMethod->getParams() as $param) {
            if (! $this->nodeTypeResolver->isObjectType($param, $objectType)) {
                continue;
            }

            $paramType = $this->nodeTypeResolver->resolve($param);
            if (! $paramType->isSuperTypeOf($objectType)->yes()) {
                continue;
            }

            $this->refactorParamTypeHint($param, $replaceIntoType);
            $this->refactorParamDocBlock($param, $classMethod, $phpDocType);
            $this->refactorMethodCalls($param, $classMethod, $methodsReturningClassInstance);
        }
    }

    private function refactorParamTypeHint(Param $param, Identifier|Name|NullableType $replaceIntoType): void
    {
        if ($this->paramAnalyzer->isNullable($param) && ! $replaceIntoType instanceof NullableType) {
            $replaceIntoType = new NullableType($replaceIntoType);
        }

        $param->type = $replaceIntoType;
    }

    private function refactorParamDocBlock(Param $param, ClassMethod $classMethod, Type $phpDocType): void
    {
        $paramName = $this->nodeNameResolver->getName($param->var);
        if ($paramName === null) {
            throw new ShouldNotHappenException();
        }

        if ($this->paramAnalyzer->isNullable($param)) {
            if ($phpDocType instanceof UnionType) {
                // Adding a UnionType into a new UnionType throws an exception so we need to "unpack" the types
                $phpDocType = new UnionType([...$phpDocType->getTypes(), new NullType()]);
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
    private function refactorMethodCalls(
        Param $param,
        ClassMethod $classMethod,
        array $methodsReturningClassInstance
    ): void {
        if ($classMethod->stmts === null) {
            return;
        }

        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($classMethod->stmts, function (Node $node) use (
            $param,
            $methodsReturningClassInstance
        ): void {
            if (! ($node instanceof MethodCall)) {
                return;
            }

            $this->refactorMethodCall($param, $node, $methodsReturningClassInstance);
        });
    }

    /**
     * @param string[] $methodsReturningClassInstance
     */
    private function refactorMethodCall(
        Param $param,
        MethodCall $methodCall,
        array $methodsReturningClassInstance
    ): void {
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

        if (! $parent instanceof Expression) {
            return;
        }

        $parent->expr = $assign;
    }

    /**
     * @param string[] $methodsReturningClassInstance
     */
    private function shouldSkipMethodCallRefactor(
        string $paramName,
        MethodCall $methodCall,
        array $methodsReturningClassInstance
    ): bool {
        if (! $this->nodeNameResolver->isName($methodCall->var, $paramName)) {
            return true;
        }

        if (! $this->nodeNameResolver->isNames($methodCall->name, $methodsReturningClassInstance)) {
            return true;
        }

        $parentNode = $methodCall->getAttribute(AttributeKey::PARENT_NODE);
        if (! $parentNode instanceof Node) {
            return true;
        }

        return $parentNode instanceof Assign;
    }
}
