<?php

declare (strict_types=1);
namespace Rector\Naming\Naming;

use DateTimeInterface;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Param;
use PhpParser\Node\UnionType;
use PHPStan\Type\ArrayType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use Rector\Naming\ExpectedNameResolver\MatchParamTypeExpectedNameResolver;
use Rector\Naming\ValueObject\ExpectedName;
use Rector\Naming\ValueObject\VariableAndCallForeach;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
final class ExpectedNameResolver
{
    /**
     * @readonly
     */
    private NodeNameResolver $nodeNameResolver;
    /**
     * @readonly
     */
    private NodeTypeResolver $nodeTypeResolver;
    /**
     * @readonly
     */
    private \Rector\Naming\Naming\PropertyNaming $propertyNaming;
    /**
     * @readonly
     */
    private MatchParamTypeExpectedNameResolver $matchParamTypeExpectedNameResolver;
    public function __construct(NodeNameResolver $nodeNameResolver, NodeTypeResolver $nodeTypeResolver, \Rector\Naming\Naming\PropertyNaming $propertyNaming, MatchParamTypeExpectedNameResolver $matchParamTypeExpectedNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->propertyNaming = $propertyNaming;
        $this->matchParamTypeExpectedNameResolver = $matchParamTypeExpectedNameResolver;
    }
    public function resolveForParamIfNotYet(Param $param) : ?string
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
        if ($currentName === $expectedName || \substr_compare($currentName, \ucfirst($expectedName), -\strlen(\ucfirst($expectedName))) === 0) {
            return null;
        }
        return $expectedName;
    }
    public function resolveForAssignNonNew(Assign $assign) : ?string
    {
        if ($assign->expr instanceof New_) {
            return null;
        }
        if (!$assign->var instanceof Variable) {
            return null;
        }
        /** @var Variable $variable */
        $variable = $assign->var;
        return $this->nodeNameResolver->getName($variable);
    }
    public function resolveForAssignNew(Assign $assign) : ?string
    {
        if (!$assign->expr instanceof New_) {
            return null;
        }
        if (!$assign->var instanceof Variable) {
            return null;
        }
        /** @var New_ $new */
        $new = $assign->expr;
        if (!$new->class instanceof Name) {
            return null;
        }
        $className = $this->nodeNameResolver->getName($new->class);
        $fullyQualifiedObjectType = new FullyQualifiedObjectType($className);
        if ($fullyQualifiedObjectType->isInstanceOf(DateTimeInterface::class)->yes()) {
            return null;
        }
        $expectedName = $this->propertyNaming->getExpectedNameFromType($fullyQualifiedObjectType);
        if (!$expectedName instanceof ExpectedName) {
            return null;
        }
        return $expectedName->getName();
    }
    /**
     * @param \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall|\PhpParser\Node\Expr\FuncCall $expr
     */
    public function resolveForCall($expr) : ?string
    {
        if ($this->isDynamicNameCall($expr)) {
            return null;
        }
        $name = $this->nodeNameResolver->getName($expr->name);
        if ($name === null) {
            return null;
        }
        $returnedType = $this->nodeTypeResolver->getType($expr);
        if (!$returnedType instanceof ObjectType) {
            return null;
        }
        $expectedName = $this->propertyNaming->getExpectedNameFromType($returnedType);
        if ($expectedName instanceof ExpectedName) {
            return $expectedName->getName();
        }
        // call with args can return different value, so skip there if not sure about the type
        if ($expr->args !== []) {
            return null;
        }
        $expectedNameFromMethodName = $this->propertyNaming->getExpectedNameFromMethodName($name);
        if ($expectedNameFromMethodName instanceof ExpectedName) {
            return $expectedNameFromMethodName->getName();
        }
        return null;
    }
    public function resolveForForeach(VariableAndCallForeach $variableAndCallForeach) : ?string
    {
        $call = $variableAndCallForeach->getCall();
        if ($this->isDynamicNameCall($call)) {
            return null;
        }
        $name = $this->nodeNameResolver->getName($call->name);
        if ($name === null) {
            return null;
        }
        $returnedType = $this->nodeTypeResolver->getType($call);
        if ($returnedType->isIterable()->no()) {
            return null;
        }
        $innerReturnedType = null;
        if ($returnedType instanceof ArrayType) {
            $innerReturnedType = $this->resolveReturnTypeFromArrayType($returnedType);
            if (!$innerReturnedType instanceof Type) {
                return null;
            }
        }
        $expectedNameFromType = $this->propertyNaming->getExpectedNameFromType($innerReturnedType ?? $returnedType);
        if ($this->isReturnedTypeAnArrayAndExpectedNameFromTypeNotNull($returnedType, $expectedNameFromType)) {
            return ($nullsafeVariable1 = $expectedNameFromType) ? $nullsafeVariable1->getSingularized() : null;
        }
        $expectedNameFromMethodName = $this->propertyNaming->getExpectedNameFromMethodName($name);
        if (!$expectedNameFromMethodName instanceof ExpectedName) {
            return ($nullsafeVariable2 = $expectedNameFromType) ? $nullsafeVariable2->getSingularized() : null;
        }
        if ($expectedNameFromMethodName->isSingular()) {
            return ($nullsafeVariable3 = $expectedNameFromType) ? $nullsafeVariable3->getSingularized() : null;
        }
        return $expectedNameFromMethodName->getSingularized();
    }
    private function isReturnedTypeAnArrayAndExpectedNameFromTypeNotNull(Type $returnedType, ?ExpectedName $expectedName) : bool
    {
        return $returnedType instanceof ArrayType && $expectedName instanceof ExpectedName;
    }
    /**
     * @param \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall|\PhpParser\Node\Expr\FuncCall $expr
     */
    private function isDynamicNameCall($expr) : bool
    {
        if ($expr->name instanceof StaticCall) {
            return \true;
        }
        if ($expr->name instanceof MethodCall) {
            return \true;
        }
        return $expr->name instanceof FuncCall;
    }
    private function resolveReturnTypeFromArrayType(ArrayType $arrayType) : ?Type
    {
        if (!$arrayType->getIterableValueType() instanceof ObjectType) {
            return null;
        }
        return $arrayType->getIterableValueType();
    }
}
