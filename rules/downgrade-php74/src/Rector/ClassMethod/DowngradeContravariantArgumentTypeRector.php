<?php

declare(strict_types=1);

namespace Rector\DowngradePhp74\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use PhpParser\Node\UnionType;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use Rector\Core\ValueObject\MethodName;
use Rector\DowngradePhp70\Rector\FunctionLike\AbstractDowngradeParamDeclarationRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see https://www.php.net/manual/en/language.oop5.variance.php#language.oop5.variance.contravariance
 *
 * @see \Rector\DowngradePhp74\Tests\Rector\ClassMethod\DowngradeContravariantArgumentTypeRector\DowngradeContravariantArgumentTypeRectorTest
 */
final class DowngradeContravariantArgumentTypeRector extends AbstractDowngradeParamDeclarationRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove contravariant argument type declarations', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class ParentType {}
class ChildType extends ParentType {}

class A
{
    public function contraVariantArguments(ChildType $type)
    { /* … */ }
}

class B extends A
{
    public function contraVariantArguments(ParentType $type)
    { /* … */ }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
class ParentType {}
class ChildType extends ParentType {}

class A
{
    public function contraVariantArguments(ChildType $type)
    { /* … */ }
}

class B extends A
{
    /**
     * @param ParentType $type
     */
    public function contraVariantArguments($type)
    { /* … */ }
}
CODE_SAMPLE
            ),
        ]);
    }

    public function shouldRemoveParamDeclaration(Param $param, FunctionLike $functionLike): bool
    {
        if ($param->variadic) {
            return false;
        }

        if ($param->type === null) {
            return false;
        }

        // Don't consider for Union types
        if ($param->type instanceof UnionType) {
            return false;
        }

        // Contravariant arguments are supported for __construct
        if ($this->isName($functionLike, MethodName::CONSTRUCT)) {
            return false;
        }

        // Check if the type is different from the one declared in some ancestor
        return $this->getDifferentParamTypeFromAncestorClass($param, $functionLike) !== null;
    }

    private function getDifferentParamTypeFromAncestorClass(Param $param, FunctionLike $functionLike): ?string
    {
        $scope = $functionLike->getAttribute(AttributeKey::SCOPE);
        if (! $scope instanceof Scope) {
            // possibly trait
            return null;
        }

        $classReflection = $scope->getClassReflection();
        if (! $classReflection instanceof ClassReflection) {
            return null;
        }

        $paramName = $this->getName($param);

        // If it is the NullableType, extract the name from its inner type
        /** @var Node $paramType */
        $paramType = $param->type;
        $isNullableType = $param->type instanceof NullableType;
        if ($isNullableType) {
            /** @var NullableType $nullableType */
            $nullableType = $paramType;
            $paramTypeName = $this->getName($nullableType->type);
        } else {
            $paramTypeName = $this->getName($paramType);
        }
        if ($paramTypeName === null) {
            return null;
        }

        /** @var string $methodName */
        $methodName = $this->getName($functionLike);

        // Either Ancestor classes or implemented interfaces
        $interfaceNames = array_map(
            function (ClassReflection $interfaceReflection): string {
                return $interfaceReflection->getName();
            },
            $classReflection->getInterfaces()
        );

        $parentClassesNames = $classReflection->getParentClassesNames();
        $parentClassLikes = array_merge($parentClassesNames, $interfaceNames);

        foreach ($parentClassLikes as $parentClassLike) {
            if (! method_exists($parentClassLike, $methodName)) {
                continue;
            }

            // Find the param we're looking for
            $parentReflectionMethod = new ReflectionMethod($parentClassLike, $methodName);
            $differentAncestorParamTypeName = $this->getDifferentParamTypeFromReflectionMethod(
                $parentReflectionMethod,
                $paramName,
                $paramTypeName
            );
            if ($differentAncestorParamTypeName !== null) {
                return $differentAncestorParamTypeName;
            }
        }

        return null;
    }

    private function getDifferentParamTypeFromReflectionMethod(
        ReflectionMethod $parentReflectionMethod,
        string $paramName,
        string $paramTypeName
    ): ?string {
        /** @var ReflectionParameter[] $parentReflectionMethodParams */
        $parentReflectionMethodParams = $parentReflectionMethod->getParameters();
        foreach ($parentReflectionMethodParams as $reflectionParameter) {
            if ($reflectionParameter->name === $paramName) {
                /**
                 * Getting a ReflectionNamedType works from PHP 7.1 onwards
                 * @see https://www.php.net/manual/en/reflectionparameter.gettype.php#125334
                 */
                $reflectionParamType = $reflectionParameter->getType();
                /**
                 * If the type is null, we don't have enough information
                 * to check if they are different. Then do nothing
                 */
                if (! $reflectionParamType instanceof ReflectionNamedType) {
                    continue;
                }
                if ($reflectionParamType->getName() !== $paramTypeName) {
                    // We found it: a different param type in some ancestor
                    return $reflectionParamType->getName();
                }
            }
        }

        return null;
    }
}
