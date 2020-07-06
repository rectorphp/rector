<?php

declare(strict_types=1);

namespace Rector\Naming\Guard;

use DateTimeInterface;
use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Property;
use PHPStan\Analyser\Scope;
use PHPStan\Type\Type;
use PHPStan\Type\TypeWithClassName;
use PHPStan\Type\UnionType;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Naming\Naming\ConflictingNameResolver;
use Rector\Naming\Naming\OverridenExistingNamesResolver;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;

/**
 * This class check if a variable name change breaks existing code in class method
 */
final class BreakingVariableRenameGuard
{
    /**
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;

    /**
     * @var ConflictingNameResolver
     */
    private $conflictingNameResolver;

    /**
     * @var OverridenExistingNamesResolver
     */
    private $overridenExistingNamesResolver;

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    public function __construct(
        BetterNodeFinder $betterNodeFinder,
        ConflictingNameResolver $conflictingNameResolver,
        OverridenExistingNamesResolver $overridenExistingNamesResolver,
        NodeNameResolver $nodeNameResolver,
        NodeTypeResolver $nodeTypeResolver
    ) {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->conflictingNameResolver = $conflictingNameResolver;
        $this->overridenExistingNamesResolver = $overridenExistingNamesResolver;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->nodeTypeResolver = $nodeTypeResolver;
    }

    public function shouldSkipVariable(
        string $currentName,
        string $expectedName,
        ClassMethod $classMethod,
        Variable $variable
    ): bool {
        // is the suffix? → also accepted
        if (Strings::endsWith($currentName, ucfirst($expectedName))) {
            return true;
        }

        if ($this->conflictingNameResolver->checkNameIsInClassMethod($expectedName, $classMethod)) {
            return true;
        }

        if ($this->overridenExistingNamesResolver->checkNameInClassMethodForNew($currentName, $classMethod)) {
            return true;
        }

        if ($this->isVariableAlreadyDefined($variable, $currentName)) {
            return true;
        }

        return $this->isUsedInIfAndOtherBranches($variable, $currentName);
    }

    public function shouldSkipProperty(Property $property, string $currentName): bool
    {
        $propertyType = $this->nodeTypeResolver->resolve($property);
        return $this->isDateTimeAtNamingConvention($propertyType, $currentName);
    }

    public function shouldSkipParam(
        string $currentName,
        string $expectedName,
        ClassMethod $classMethod,
        Param $param
    ): bool {
        // is the suffix? → also accepted
        if (Strings::endsWith($currentName, ucfirst($expectedName))) {
            return true;
        }

        $conflictingNames = $this->conflictingNameResolver->resolveConflictingVariableNamesForParam($classMethod);
        if (in_array($expectedName, $conflictingNames, true)) {
            return true;
        }

        if ($this->conflictingNameResolver->checkNameIsInClassMethod($expectedName, $classMethod)) {
            return true;
        }

        if ($this->overridenExistingNamesResolver->checkNameInClassMethodForParam($expectedName, $classMethod)) {
            return true;
        }

        if ($this->isVariableAlreadyDefined($param->var, $currentName)) {
            return true;
        }

        $paramType = $this->nodeTypeResolver->getStaticType($param);

        return $this->isDateTimeAtNamingConvention($paramType, $currentName);
    }

    private function isVariableAlreadyDefined(Variable $variable, string $currentVariableName): bool
    {
        $scope = $variable->getAttribute(AttributeKey::SCOPE);
        if (! $scope instanceof Scope) {
            return false;
        }

        $trinaryLogic = $scope->hasVariableType($currentVariableName);
        if ($trinaryLogic->yes()) {
            return true;
        }

        return $trinaryLogic->maybe();
    }

    private function isUsedInIfAndOtherBranches(Variable $variable, string $currentVariableName): bool
    {
        // is in if branches?
        $previousIf = $this->betterNodeFinder->findFirstPreviousOfTypes($variable, [If_::class]);
        if ($previousIf instanceof If_) {
            $variableUses = [];

            $variableUses[] = $this->betterNodeFinder->findFirst($previousIf->stmts, function (Node $node) use (
                $currentVariableName
            ) {
                return $this->isVariableName($node, $currentVariableName);
            });

            $variableUses[] = $this->betterNodeFinder->findFirst(
                $previousIf->else !== null ? $previousIf->else->stmts : [],
                function (Node $node) use ($currentVariableName) {
                    return $this->isVariableName($node, $currentVariableName);
                }
            );

            $variableUses[] = $this->betterNodeFinder->findFirst($previousIf->elseifs, function (Node $node) use (
                $currentVariableName
            ) {
                return $this->isVariableName($node, $currentVariableName);
            });

            $variableUses = array_filter($variableUses);
            if (count($variableUses) > 1) {
                return true;
            }
        }

        return false;
    }

    private function isVariableName(Node $node, string $name): bool
    {
        if (! $node instanceof Variable) {
            return false;
        }

        return $this->nodeNameResolver->isName($node, $name);
    }

    private function isDateTimeAtNamingConvention(Type $type, string $currentName): bool
    {
        $type = $this->unwrapUnionObjectType($type);
        if (! $type instanceof TypeWithClassName) {
            return false;
        }

        if (! is_a($type->getClassName(), DateTimeInterface::class, true)) {
            return false;
        }

        return (bool) Strings::match($currentName, '#[\w+]At$#');
    }

    private function unwrapUnionObjectType(Type $type): Type
    {
        if (! $type instanceof UnionType) {
            return $type;
        }

        foreach ($type->getTypes() as $unionedType) {
            if (! $unionedType instanceof TypeWithClassName) {
                continue;
            }

            return $unionedType;
        }

        return $type;
    }
}
