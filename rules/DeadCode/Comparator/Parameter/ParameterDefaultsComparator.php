<?php

declare(strict_types=1);

namespace Rector\DeadCode\Comparator\Parameter;

use PhpParser\Node\Expr;
use PhpParser\Node\Param;
use PHPStan\Reflection\ParameterReflection;
use Rector\Core\PhpParser\Node\Value\ValueResolver;
use Rector\DowngradePhp80\Reflection\DefaultParameterValueResolver;

final class ParameterDefaultsComparator
{
    public function __construct(
        private ValueResolver $valueResolver,
        private DefaultParameterValueResolver $defaultParameterValueResolver
    ) {
    }

    public function areDefaultValuesDifferent(ParameterReflection $parameterReflection, Param $param): bool
    {
        if ($parameterReflection->getDefaultValue() === null && $param->default === null) {
            return false;
        }

        if ($this->isMutuallyExclusiveNull($parameterReflection, $param)) {
            return true;
        }

        /** @var Expr $paramDefault */
        $paramDefault = $param->default;

        $firstParameterValue = $this->defaultParameterValueResolver->resolveFromParameterReflection(
            $parameterReflection
        );
        $secondParameterValue = $this->valueResolver->getValue($paramDefault);

        return $firstParameterValue !== $secondParameterValue;
    }

//    /**
//     * @return bool|float|int|string|mixed[]|null
//     */
//    public function resolveParameterReflectionDefaultValue(ParameterReflection $parameterReflection)
//    {
//        $defaultValue = $parameterReflection->getDefaultValue();
//        if (! $defaultValue instanceof ConstantType) {
//            throw new ShouldNotHappenException();
//        }
//
//        if ($defaultValue instanceof ConstantArrayType) {
//            return $defaultValue->getAllArrays();
//        }
//
//        /** @var ConstantStringType|ConstantIntegerType|ConstantFloatType|ConstantBooleanType|NullType $defaultValue */
//        return $defaultValue->getValue();
//    }

    private function isMutuallyExclusiveNull(ParameterReflection $parameterReflection, Param $param): bool
    {
        if ($parameterReflection->getDefaultValue() === null && $param->default !== null) {
            return true;
        }

        if ($parameterReflection->getDefaultValue() === null) {
            return false;
        }

        return $param->default === null;
    }
}
