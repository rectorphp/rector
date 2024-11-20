<?php

declare (strict_types=1);
namespace Rector\DowngradePhp80\Reflection;

use PhpParser\BuilderHelpers;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Name;
use PHPStan\Reflection\ParameterReflection;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\Constant\ConstantIntegerType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\NullType;
use PHPStan\Type\Type;
use PHPStan\Type\VerbosityLevel;
use Rector\Exception\ShouldNotHappenException;
final class DefaultParameterValueResolver
{
    public function resolveFromParameterReflection(ParameterReflection $parameterReflection) : ?\PhpParser\Node\Expr
    {
        $defaultValueType = $parameterReflection->getDefaultValue();
        if (!$defaultValueType instanceof Type) {
            return null;
        }
        if (!$defaultValueType->isConstantValue()->yes()) {
            throw new ShouldNotHappenException();
        }
        return $this->resolveValueFromType($defaultValueType);
    }
    /**
     * @return \PhpParser\Node\Expr\ConstFetch|\PhpParser\Node\Expr
     */
    private function resolveValueFromType(Type $constantType)
    {
        if ($constantType instanceof ConstantBooleanType) {
            return $this->resolveConstantBooleanType($constantType);
        }
        if ($constantType instanceof ConstantArrayType) {
            $values = [];
            foreach ($constantType->getValueTypes() as $valueType) {
                if (!$valueType->isConstantValue()->yes()) {
                    throw new ShouldNotHappenException();
                }
                $values[] = $this->resolveValueFromType($valueType);
            }
            return BuilderHelpers::normalizeValue($values);
        }
        /** @var ConstantStringType|ConstantIntegerType|NullType $constantType */
        return BuilderHelpers::normalizeValue($constantType->getValue());
    }
    private function resolveConstantBooleanType(ConstantBooleanType $constantBooleanType) : ConstFetch
    {
        $value = $constantBooleanType->describe(VerbosityLevel::value());
        return new ConstFetch(new Name($value));
    }
}
