<?php

declare(strict_types=1);

namespace Rector\Naming\Naming;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\UnionType;
use PHPStan\Type\ArrayType;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use Rector\Naming\ExpectedNameResolver\MatchParamTypeExpectedNameResolver;
use Rector\Naming\ValueObject\ExpectedName;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;

final class ExpectedNameResolver
{
    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var PropertyNaming
     */
    private $propertyNaming;

    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    /**
     * @var MatchParamTypeExpectedNameResolver
     */
    private $matchParamTypeExpectedNameResolver;

    public function __construct(
        NodeNameResolver $nodeNameResolver,
        NodeTypeResolver $nodeTypeResolver,
        PropertyNaming $propertyNaming,
        MatchParamTypeExpectedNameResolver $matchParamTypeExpectedNameResolver
    ) {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->propertyNaming = $propertyNaming;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->matchParamTypeExpectedNameResolver = $matchParamTypeExpectedNameResolver;
    }

    public function resolveForParamIfNotYet(Param $param): ?string
    {
        if ($param->type instanceof UnionType) {
            return null;
        }

        $expectedName = $this->matchParamTypeExpectedNameResolver->resolve($param);
        if ($expectedName === null) {
            return null;
        }

        /** @var string $currentName */
        $currentName = $this->nodeNameResolver->getName($param->var);
        if ($currentName === $expectedName) {
            return null;
        }

        if ($this->nodeNameResolver->endsWith($currentName, $expectedName)) {
            return null;
        }

        return $expectedName;
    }

    public function resolveForAssignNonNew(Assign $assign): ?string
    {
        if ($assign->expr instanceof New_) {
            return null;
        }

        if (! $assign->var instanceof Variable) {
            return null;
        }

        /** @var Variable $variable */
        $variable = $assign->var;

        return $this->nodeNameResolver->getName($variable);
    }

    public function resolveForAssignNew(Assign $assign): ?string
    {
        if (! $assign->expr instanceof New_) {
            return null;
        }

        if (! $assign->var instanceof Variable) {
            return null;
        }

        /** @var New_ $new */
        $new = $assign->expr;
        if (! $new->class instanceof Name) {
            return null;
        }

        $className = $this->nodeNameResolver->getName($new->class);
        $fullyQualifiedObjectType = new FullyQualifiedObjectType($className);

        $expectedName = $this->propertyNaming->getExpectedNameFromType($fullyQualifiedObjectType);
        if (! $expectedName instanceof ExpectedName) {
            return null;
        }

        return $expectedName->getName();
    }

    /**
     * @param MethodCall|StaticCall|FuncCall $expr
     */
    public function resolveForCall(Expr $expr): ?string
    {
        if ($this->isDynamicNameCall($expr)) {
            return null;
        }

        $name = $this->nodeNameResolver->getName($expr->name);
        if ($name === null) {
            return null;
        }

        $returnedType = $this->nodeTypeResolver->getStaticType($expr);

        if ($returnedType instanceof ArrayType) {
            return null;
        }

        if ($returnedType instanceof MixedType) {
            return null;
        }

        $expectedName = $this->propertyNaming->getExpectedNameFromType($returnedType);

        if ($expectedName !== null) {
            return $expectedName->getName();
        }

        // call with args can return different value, so skip there if not sure about the type
        if ($expr->args !== []) {
            return null;
        }

        $expectedNameFromMethodName = $this->propertyNaming->getExpectedNameFromMethodName($name);
        if ($expectedNameFromMethodName !== null) {
            return $expectedNameFromMethodName->getName();
        }

        return null;
    }

    /**
     * @param MethodCall|StaticCall|FuncCall $expr
     */
    public function resolveForForeach(Expr $expr): ?string
    {
        if ($this->isDynamicNameCall($expr)) {
            return null;
        }

        $name = $this->nodeNameResolver->getName($expr->name);
        if ($name === null) {
            return null;
        }

        $returnedType = $this->nodeTypeResolver->getStaticType($expr);
        if ($returnedType->isIterable()->no()) {
            return null;
        }

        if ($returnedType instanceof ArrayType) {
            $returnedType = $this->resolveReturnTypeFromArrayType($expr, $returnedType);
            if (! $returnedType instanceof Type) {
                return null;
            }
        }

        $expectedNameFromType = $this->propertyNaming->getExpectedNameFromType($returnedType);

        if ($expectedNameFromType !== null) {
            return $expectedNameFromType->getSingularized();
        }

        $expectedNameFromMethodName = $this->propertyNaming->getExpectedNameFromMethodName($name);
        if (! $expectedNameFromMethodName instanceof ExpectedName) {
            return null;
        }

        if ($expectedNameFromMethodName->isSingular()) {
            return null;
        }

        return $expectedNameFromMethodName->getSingularized();
    }

    /**
     * @param MethodCall|StaticCall|FuncCall $expr
     */
    private function isDynamicNameCall(Expr $expr): bool
    {
        if ($expr->name instanceof StaticCall) {
            return true;
        }

        if ($expr->name instanceof MethodCall) {
            return true;
        }

        return $expr->name instanceof FuncCall;
    }

    private function resolveReturnTypeFromArrayType(Expr $expr, ArrayType $arrayType): ?Type
    {
        $parentNode = $expr->getAttribute(AttributeKey::PARENT_NODE);
        if (! $parentNode instanceof Foreach_) {
            return null;
        }

        if (! $arrayType->getItemType() instanceof ObjectType) {
            return null;
        }

        return $arrayType->getItemType();
    }
}
