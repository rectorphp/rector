<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\DowngradePhp80\Reflection;

use RectorPrefix20220606\PhpParser\BuilderHelpers;
use RectorPrefix20220606\PhpParser\Node\Expr;
use RectorPrefix20220606\PhpParser\Node\Expr\ConstFetch;
use RectorPrefix20220606\PhpParser\Node\Name;
use RectorPrefix20220606\PHPStan\Reflection\ParameterReflection;
use RectorPrefix20220606\PHPStan\Type\Constant\ConstantArrayType;
use RectorPrefix20220606\PHPStan\Type\Constant\ConstantBooleanType;
use RectorPrefix20220606\PHPStan\Type\ConstantType;
use RectorPrefix20220606\PHPStan\Type\Type;
use RectorPrefix20220606\PHPStan\Type\VerbosityLevel;
use RectorPrefix20220606\Rector\Core\Exception\ShouldNotHappenException;
final class DefaultParameterValueResolver
{
    /**
     * @return \PhpParser\Node\Expr|null
     */
    public function resolveFromParameterReflection(ParameterReflection $parameterReflection)
    {
        $defaultValue = $parameterReflection->getDefaultValue();
        if (!$defaultValue instanceof Type) {
            return null;
        }
        if (!$defaultValue instanceof ConstantType) {
            throw new ShouldNotHappenException();
        }
        return $this->resolveValueFromType($defaultValue);
    }
    /**
     * @return \PhpParser\Node\Expr\ConstFetch|\PhpParser\Node\Expr
     */
    private function resolveValueFromType(ConstantType $constantType)
    {
        if ($constantType instanceof ConstantBooleanType) {
            return $this->resolveConstantBooleanType($constantType);
        }
        if ($constantType instanceof ConstantArrayType) {
            $values = [];
            foreach ($constantType->getValueTypes() as $valueType) {
                if (!$valueType instanceof ConstantType) {
                    throw new ShouldNotHappenException();
                }
                $values[] = $this->resolveValueFromType($valueType);
            }
            return BuilderHelpers::normalizeValue($values);
        }
        return BuilderHelpers::normalizeValue($constantType->getValue());
    }
    private function resolveConstantBooleanType(ConstantBooleanType $constantBooleanType) : ConstFetch
    {
        $value = $constantBooleanType->describe(VerbosityLevel::value());
        $name = new Name($value);
        return new ConstFetch($name);
    }
}
