<?php

declare (strict_types=1);
namespace Rector\DowngradePhp80\Reflection;

use PHPStan\Reflection\ParameterReflection;
use PHPStan\Reflection\PassedByReference;
use PHPStan\Type\ConstantTypeHelper;
use PHPStan\Type\Type;
use Rector\Exception\NotImplementedYetException;
use ReflectionFunction;
use ReflectionParameter;
use Throwable;
final class SimplePhpParameterReflection implements ParameterReflection
{
    /**
     * @readonly
     * @var \ReflectionParameter
     */
    private $reflectionParameter;
    public function __construct(ReflectionFunction $reflectionFunction, int $position)
    {
        $this->reflectionParameter = $reflectionFunction->getParameters()[$position];
    }
    public function getName() : string
    {
        return $this->reflectionParameter->getName();
    }
    public function isOptional() : bool
    {
        return $this->reflectionParameter->isOptional();
    }
    /**
     * getType() is never used yet on manual object creation, and the implementation require PHPStan $phpDocType services injection
     * @see https://github.com/phpstan/phpstan-src/blob/92420cd4b190b57d1ba8bf9e800eb97c8c0ee2f2/src/Reflection/Php/PhpParameterReflection.php#L24
     */
    public function getType() : Type
    {
        throw new NotImplementedYetException();
    }
    public function passedByReference() : PassedByReference
    {
        return $this->reflectionParameter->isPassedByReference() ? PassedByReference::createCreatesNewVariable() : PassedByReference::createNo();
    }
    public function isVariadic() : bool
    {
        return $this->reflectionParameter->isVariadic();
    }
    public function getDefaultValue() : ?Type
    {
        try {
            if ($this->reflectionParameter->isDefaultValueAvailable()) {
                $defaultValue = $this->reflectionParameter->getDefaultValue();
                return ConstantTypeHelper::getTypeFromValue($defaultValue);
            }
        } catch (Throwable $exception) {
            return null;
        }
        return null;
    }
}
