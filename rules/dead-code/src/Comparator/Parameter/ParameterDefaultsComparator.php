<?php

declare(strict_types=1);

namespace Rector\DeadCode\Comparator\Parameter;

use PhpParser\Node\Expr;
use PhpParser\Node\Param;
use PHPStan\Reflection\ParameterReflection;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\Constant\ConstantFloatType;
use PHPStan\Type\Constant\ConstantIntegerType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\ConstantType;
use PHPStan\Type\NullType;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\PhpParser\Node\Value\ValueResolver;

final class ParameterDefaultsComparator
{
    /**
     * @var ValueResolver
     */
    private $valueResolver;

    public function __construct(ValueResolver $valueResolver)
    {
        $this->valueResolver = $valueResolver;
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

        $firstParameterValue = $this->resolveParameterReflectionDefaultValue($parameterReflection);
        $secondParameterValue = $this->valueResolver->getValue($paramDefault);

        return $firstParameterValue !== $secondParameterValue;
    }

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

    /**
     * @return bool|float|int|string|mixed[]|null
     */
    private function resolveParameterReflectionDefaultValue(ParameterReflection $parameterReflection)
    {
        $defaultValue = $parameterReflection->getDefaultValue();
        if (! $defaultValue instanceof ConstantType) {
            throw new ShouldNotHappenException();
        }

        if ($defaultValue instanceof ConstantArrayType) {
            return $defaultValue->getAllArrays();
        }

        /** @var ConstantStringType|ConstantIntegerType|ConstantFloatType|ConstantBooleanType|NullType $defaultValue */
        return $defaultValue->getValue();
    }
}
