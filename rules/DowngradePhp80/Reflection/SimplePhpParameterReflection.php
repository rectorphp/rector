<?php

declare (strict_types=1);
namespace Rector\DowngradePhp80\Reflection;

use PHPStan\Reflection\ParameterReflection;
use PHPStan\Reflection\PassedByReference;
use PHPStan\Type\ConstantTypeHelper;
use PHPStan\Type\Type;
use Rector\Core\Exception\NotImplementedYetException;
use ReflectionFunction;
use ReflectionParameter;
use Throwable;
final class SimplePhpParameterReflection implements \PHPStan\Reflection\ParameterReflection
{
    /**
     * @readonly
     * @var \ReflectionParameter
     */
    private $parameter;
    public function __construct(\ReflectionFunction $reflectionFunction, int $position)
    {
        $this->parameter = $reflectionFunction->getParameters()[$position];
    }
    public function getName() : string
    {
        return $this->parameter->getName();
    }
    public function isOptional() : bool
    {
        return $this->parameter->isOptional();
    }
    /**
     * getType() is never used yet on manual object creation, and the implementation require PHPStan $phpDocType services injection
     * @see https://github.com/phpstan/phpstan-src/blob/92420cd4b190b57d1ba8bf9e800eb97c8c0ee2f2/src/Reflection/Php/PhpParameterReflection.php#L24
     */
    public function getType() : \PHPStan\Type\Type
    {
        throw new \Rector\Core\Exception\NotImplementedYetException();
    }
    public function passedByReference() : \PHPStan\Reflection\PassedByReference
    {
        return $this->parameter->isPassedByReference() ? \PHPStan\Reflection\PassedByReference::createCreatesNewVariable() : \PHPStan\Reflection\PassedByReference::createNo();
    }
    public function isVariadic() : bool
    {
        return $this->parameter->isVariadic();
    }
    public function getDefaultValue() : ?\PHPStan\Type\Type
    {
        try {
            if ($this->parameter->isDefaultValueAvailable()) {
                $defaultValue = $this->parameter->getDefaultValue();
                return \PHPStan\Type\ConstantTypeHelper::getTypeFromValue($defaultValue);
            }
        } catch (\Throwable $exception) {
            return null;
        }
        return null;
    }
}
