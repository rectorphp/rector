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
use PHPStan\Type\ConstantType;
use PHPStan\Type\Type;
use PHPStan\Type\VerbosityLevel;
use Rector\Core\Exception\ShouldNotHappenException;
final class DefaultParameterValueResolver
{
    /**
     * @return \PhpParser\Node\Expr|null
     */
    public function resolveFromParameterReflection(\PHPStan\Reflection\ParameterReflection $parameterReflection)
    {
        $defaultValue = $parameterReflection->getDefaultValue();
        if (!$defaultValue instanceof \PHPStan\Type\Type) {
            return null;
        }
        if (!$defaultValue instanceof \PHPStan\Type\ConstantType) {
            throw new \Rector\Core\Exception\ShouldNotHappenException();
        }
        return $this->resolveValueFromType($defaultValue);
    }
    /**
     * @return \PhpParser\Node\Expr\ConstFetch|\PhpParser\Node\Expr
     */
    private function resolveValueFromType(\PHPStan\Type\ConstantType $constantType)
    {
        if ($constantType instanceof \PHPStan\Type\Constant\ConstantBooleanType) {
            return $this->resolveConstantBooleanType($constantType);
        }
        if ($constantType instanceof \PHPStan\Type\Constant\ConstantArrayType) {
            $values = [];
            foreach ($constantType->getValueTypes() as $valueType) {
                if (!$valueType instanceof \PHPStan\Type\ConstantType) {
                    throw new \Rector\Core\Exception\ShouldNotHappenException();
                }
                $values[] = $this->resolveValueFromType($valueType);
            }
            return \PhpParser\BuilderHelpers::normalizeValue($values);
        }
        return \PhpParser\BuilderHelpers::normalizeValue($constantType->getValue());
    }
    private function resolveConstantBooleanType(\PHPStan\Type\Constant\ConstantBooleanType $constantBooleanType) : \PhpParser\Node\Expr\ConstFetch
    {
        $value = $constantBooleanType->describe(\PHPStan\Type\VerbosityLevel::value());
        $name = new \PhpParser\Node\Name($value);
        return new \PhpParser\Node\Expr\ConstFetch($name);
    }
}
