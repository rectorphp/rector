<?php

declare (strict_types=1);
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
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    /**
     * @var \Rector\Naming\Naming\PropertyNaming
     */
    private $propertyNaming;
    /**
     * @var \Rector\Naming\ExpectedNameResolver\MatchParamTypeExpectedNameResolver
     */
    private $matchParamTypeExpectedNameResolver;
    public function __construct(\Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\NodeTypeResolver\NodeTypeResolver $nodeTypeResolver, \Rector\Naming\Naming\PropertyNaming $propertyNaming, \Rector\Naming\ExpectedNameResolver\MatchParamTypeExpectedNameResolver $matchParamTypeExpectedNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->propertyNaming = $propertyNaming;
        $this->matchParamTypeExpectedNameResolver = $matchParamTypeExpectedNameResolver;
    }
    public function resolveForParamIfNotYet(\PhpParser\Node\Param $param) : ?string
    {
        if ($param->type instanceof \PhpParser\Node\UnionType) {
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
    public function resolveForAssignNonNew(\PhpParser\Node\Expr\Assign $assign) : ?string
    {
        if ($assign->expr instanceof \PhpParser\Node\Expr\New_) {
            return null;
        }
        if (!$assign->var instanceof \PhpParser\Node\Expr\Variable) {
            return null;
        }
        /** @var Variable $variable */
        $variable = $assign->var;
        return $this->nodeNameResolver->getName($variable);
    }
    public function resolveForAssignNew(\PhpParser\Node\Expr\Assign $assign) : ?string
    {
        if (!$assign->expr instanceof \PhpParser\Node\Expr\New_) {
            return null;
        }
        if (!$assign->var instanceof \PhpParser\Node\Expr\Variable) {
            return null;
        }
        /** @var New_ $new */
        $new = $assign->expr;
        if (!$new->class instanceof \PhpParser\Node\Name) {
            return null;
        }
        $className = $this->nodeNameResolver->getName($new->class);
        $fullyQualifiedObjectType = new \Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType($className);
        $expectedName = $this->propertyNaming->getExpectedNameFromType($fullyQualifiedObjectType);
        if (!$expectedName instanceof \Rector\Naming\ValueObject\ExpectedName) {
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
        if ($returnedType instanceof \PHPStan\Type\ArrayType) {
            return null;
        }
        if ($returnedType instanceof \PHPStan\Type\MixedType) {
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
     * @param \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall|\PhpParser\Node\Expr\FuncCall $expr
     */
    public function resolveForForeach($expr) : ?string
    {
        if ($this->isDynamicNameCall($expr)) {
            return null;
        }
        $name = $this->nodeNameResolver->getName($expr->name);
        if ($name === null) {
            return null;
        }
        $returnedType = $this->nodeTypeResolver->getType($expr);
        if ($returnedType->isIterable()->no()) {
            return null;
        }
        $innerReturnedType = null;
        if ($returnedType instanceof \PHPStan\Type\ArrayType) {
            $innerReturnedType = $this->resolveReturnTypeFromArrayType($expr, $returnedType);
            if (!$innerReturnedType instanceof \PHPStan\Type\Type) {
                return null;
            }
        }
        $expectedNameFromType = $this->propertyNaming->getExpectedNameFromType($innerReturnedType ?? $returnedType);
        if ($this->isReturnedTypeAnArrayAndExpectedNameFromTypeNotNull($returnedType, $expectedNameFromType)) {
            return ($expectedNameFromType2 = $expectedNameFromType) ? $expectedNameFromType2->getSingularized() : null;
        }
        $expectedNameFromMethodName = $this->propertyNaming->getExpectedNameFromMethodName($name);
        if (!$expectedNameFromMethodName instanceof \Rector\Naming\ValueObject\ExpectedName) {
            return ($expectedNameFromType2 = $expectedNameFromType) ? $expectedNameFromType2->getSingularized() : null;
        }
        if ($expectedNameFromMethodName->isSingular()) {
            return ($expectedNameFromType2 = $expectedNameFromType) ? $expectedNameFromType2->getSingularized() : null;
        }
        return $expectedNameFromMethodName->getSingularized();
    }
    private function isReturnedTypeAnArrayAndExpectedNameFromTypeNotNull(\PHPStan\Type\Type $returnedType, ?\Rector\Naming\ValueObject\ExpectedName $expectedName) : bool
    {
        return $returnedType instanceof \PHPStan\Type\ArrayType && $expectedName !== null;
    }
    /**
     * @param \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall|\PhpParser\Node\Expr\FuncCall $expr
     */
    private function isDynamicNameCall($expr) : bool
    {
        if ($expr->name instanceof \PhpParser\Node\Expr\StaticCall) {
            return \true;
        }
        if ($expr->name instanceof \PhpParser\Node\Expr\MethodCall) {
            return \true;
        }
        return $expr->name instanceof \PhpParser\Node\Expr\FuncCall;
    }
    private function resolveReturnTypeFromArrayType(\PhpParser\Node\Expr $expr, \PHPStan\Type\ArrayType $arrayType) : ?\PHPStan\Type\Type
    {
        $parentNode = $expr->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if (!$parentNode instanceof \PhpParser\Node\Stmt\Foreach_) {
            return null;
        }
        if (!$arrayType->getItemType() instanceof \PHPStan\Type\ObjectType) {
            return null;
        }
        return $arrayType->getItemType();
    }
}
