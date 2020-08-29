<?php

declare(strict_types=1);

namespace Rector\Core\PHPStan\Reflection\TypeToCallReflectionResolver;

use Nette\Utils\Strings;
use PhpParser\Node\Name;
use PHPStan\Reflection\ClassMemberAccessAnswerer;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\Type;
use Rector\Core\Contract\PHPStan\Reflection\TypeToCallReflectionResolver\TypeToCallReflectionResolverInterface;

/**
 * @see https://github.com/phpstan/phpstan-src/blob/b1fd47bda2a7a7d25091197b125c0adf82af6757/src/Type/Constant/ConstantStringType.php#L147
 */
final class ConstantStringTypeToCallReflectionResolver implements TypeToCallReflectionResolverInterface
{
    /**
     * Took from https://github.com/phpstan/phpstan-src/blob/8376548f76e2c845ae047e3010e873015b796818/src/Type/Constant/ConstantStringType.php#L158
     *
     * @see https://regex101.com/r/IE6lcM/4
     *
     * @var string
     */
    private const STATIC_METHOD_REGEXP = '#^([a-zA-Z_\\x7f-\\xff\\\\][a-zA-Z0-9_\\x7f-\\xff\\\\]*)::([a-zA-Z_\\x7f-\\xff][a-zA-Z0-9_\\x7f-\\xff]*)\\z#';

    /**
     * @var ReflectionProvider
     */
    private $reflectionProvider;

    public function __construct(ReflectionProvider $reflectionProvider)
    {
        $this->reflectionProvider = $reflectionProvider;
    }

    public function supports(Type $type): bool
    {
        return $type instanceof ConstantStringType;
    }

    /**
     * @param ConstantStringType $type
     * @return FunctionReflection|MethodReflection|null
     */
    public function resolve(Type $type, ClassMemberAccessAnswerer $classMemberAccessAnswerer)
    {
        $value = $type->getValue();

        // 'my_function'
        $name = new Name($value);
        if ($this->reflectionProvider->hasFunction($name, null)) {
            return $this->reflectionProvider->getFunction($name, null);
        }

        // 'MyClass::myStaticFunction'
        $matches = Strings::match($value, self::STATIC_METHOD_REGEXP);
        if ($matches === null) {
            return null;
        }

        if (! $this->reflectionProvider->hasClass($matches[1])) {
            return null;
        }

        $classReflection = $this->reflectionProvider->getClass($matches[1]);
        if (! $classReflection->hasMethod($matches[2])) {
            return null;
        }

        return $classReflection->getMethod($matches[2], $classMemberAccessAnswerer);
    }
}
