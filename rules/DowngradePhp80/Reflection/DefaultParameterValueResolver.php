<?php

declare(strict_types=1);

namespace Rector\DowngradePhp80\Reflection;

use PhpParser\BuilderHelpers;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Name;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParameterReflection;
use PHPStan\Reflection\ParametersAcceptor;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\ConstantType;
use PHPStan\Type\Type;
use Rector\Core\Exception\NotImplementedYetException;
use Rector\Core\Exception\ShouldNotHappenException;

final class DefaultParameterValueResolver
{
    public function resolveFromParameterReflection(ParameterReflection $parameterReflection): Expr | null
    {
        $defaultValue = $parameterReflection->getDefaultValue();
        if (! $defaultValue instanceof Type) {
            return null;
        }

        if (! $defaultValue instanceof ConstantType) {
            throw new ShouldNotHappenException();
        }

        return $this->resolveValueFromType($defaultValue);
    }

    public function resolveFromFunctionLikeAndPosition(
        MethodReflection | FunctionReflection $functionLikeReflection,
        int $position
    ): ?Expr {
        $parametersAcceptor = $functionLikeReflection->getVariants()[0] ?? null;
        if (! $parametersAcceptor instanceof ParametersAcceptor) {
            return null;
        }

        $parameterReflection = $parametersAcceptor->getParameters()[$position] ?? null;
        if (! $parameterReflection instanceof ParameterReflection) {
            return null;
        }

        return $this->resolveFromParameterReflection($parameterReflection);
    }

    private function resolveValueFromType(ConstantType $constantType): ConstFetch | Expr
    {
        if ($constantType instanceof ConstantBooleanType) {
            return $this->resolveConstantBooleanType($constantType);
        }

        if ($constantType instanceof ConstantArrayType) {
            $values = [];
            foreach ($constantType->getValueTypes() as $valueType) {
                if (! $valueType instanceof ConstantType) {
                    throw new ShouldNotHappenException();
                }

                $values[] = $this->resolveValueFromType($valueType);
            }

            return BuilderHelpers::normalizeValue($values);
        }

        return BuilderHelpers::normalizeValue($constantType->getValue());
    }

    private function resolveConstantBooleanType(ConstantBooleanType $constantBooleanType): ConstFetch
    {
        if (! $constantBooleanType->getValue()) {
            $name = new Name('false');
        } elseif ($constantBooleanType->getValue()) {
            $name = new Name('true');
        } else {
            throw new NotImplementedYetException();
        }

        return new ConstFetch($name);
    }
}
