<?php

declare(strict_types=1);

namespace Rector\DowngradePhp74\Rector\ClassMethod;

use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use PhpParser\Node\Param;
use PHPStan\Analyser\Scope;
use PhpParser\Node\UnionType;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Rector\DowngradePhp71\Rector\FunctionLike\AbstractDowngradeParamDeclarationRector;

/**
 * @see https://www.php.net/manual/en/language.oop5.variance.php#language.oop5.variance.contravariance
 *
 * @see \Rector\DowngradePhp74\Tests\Rector\ClassMethod\DowngradeContravarianArgumentTypeRector\DowngradeContravarianArgumentTypeRectorTest
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

        // Check if the type is different from the one declared in some ancestor
        return $this->getDifferentParamTypeFromAncestorClass($param, $functionLike) !== null;
    }

    /**
     * @param ClassMethod|Function_ $functionLike
     */
    private function getDifferentParamTypeFromAncestorClass(Param $param, FunctionLike $functionLike): ?string
    {
        /** @var Scope|null $scope */
        $scope = $functionLike->getAttribute(AttributeKey::SCOPE);
        if ($scope === null) {
            // possibly trait
            return null;
        }

        $classReflection = $scope->getClassReflection();
        if ($classReflection === null) {
            return null;
        }

        $paramName = $this->getName($param);

        // If it is the NullableType, extract the name from its inner type
        $isNullableType = $param->type instanceof NullableType;
        if ($isNullableType) {
            /** @var NullableType */
            $nullableType = $param->type;
            $paramTypeName = $this->getName($nullableType->type);
        } else {
            $paramTypeName = $this->getName($param->type);
        }

        /** @var string $methodName */
        $methodName = $this->getName($functionLike->name);

        foreach ($classReflection->getParentClassesNames() as $parentClassName) {
            if (! method_exists($parentClassName, $methodName)) {
                continue;
            }

            // Find the param we're looking for
            $parentReflectionMethod = new ReflectionMethod($parentClassName, $methodName);
            /** @var ReflectionParameter[] */
            $parentReflectionMethodParams = $parentReflectionMethod->getParameters();
            if ($parentReflectionMethodParams === null) {
                continue;
            }
            foreach ($parentReflectionMethodParams as $reflectionParameter) {
                if ($reflectionParameter->name == $paramName) {
                    /** @var ReflectionNamedType */
                    $reflectionParamType = $reflectionParameter->getType();
                    if ($reflectionParamType->getName() != $paramTypeName) {
                        // We found it: a different param type in some ancestor
                        return $reflectionParamType->getName();
                    }
                }
            }
        }

        return null;
    }
}
