<?php

declare (strict_types=1);
namespace Rector\Core\PHPStan\Reflection\TypeToCallReflectionResolver;

use RectorPrefix202308\Nette\Utils\Strings;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\Type;
use Rector\Core\Contract\PHPStan\Reflection\TypeToCallReflectionResolver\TypeToCallReflectionResolverInterface;
/**
 * @see https://github.com/phpstan/phpstan-src/blob/b1fd47bda2a7a7d25091197b125c0adf82af6757/src/Type/Constant/ConstantStringType.php#L147
 *
 * @implements TypeToCallReflectionResolverInterface<ConstantStringType>
 */
final class ConstantStringTypeToCallReflectionResolver implements TypeToCallReflectionResolverInterface
{
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    /**
     * Took from https://github.com/phpstan/phpstan-src/blob/8376548f76e2c845ae047e3010e873015b796818/src/Type/Constant/ConstantStringType.php#L158
     *
     * @see https://regex101.com/r/IE6lcM/4
     *
     * @var string
     */
    private const STATIC_METHOD_REGEX = '#^(?<' . self::CLASS_KEY . '>[a-zA-Z_\\x7f-\\xff\\\\][a-zA-Z0-9_\\x7f-\\xff\\\\]*)::(?<' . self::METHOD_KEY . '>[a-zA-Z_\\x7f-\\xff][a-zA-Z0-9_\\x7f-\\xff]*)\\z#';
    /**
     * @var string
     */
    private const CLASS_KEY = 'class';
    /**
     * @var string
     */
    private const METHOD_KEY = 'method';
    public function __construct(ReflectionProvider $reflectionProvider)
    {
        $this->reflectionProvider = $reflectionProvider;
    }
    public function supports(Type $type) : bool
    {
        return $type instanceof ConstantStringType;
    }
    /**
     * @param ConstantStringType $type
     * @return \PHPStan\Reflection\FunctionReflection|\PHPStan\Reflection\MethodReflection|null
     */
    public function resolve(Type $type, Scope $scope)
    {
        $value = $type->getValue();
        // 'my_function'
        $name = new Name($value);
        if ($this->reflectionProvider->hasFunction($name, null)) {
            return $this->reflectionProvider->getFunction($name, null);
        }
        // 'MyClass::myStaticFunction'
        $matches = Strings::match($value, self::STATIC_METHOD_REGEX);
        if ($matches === null) {
            return null;
        }
        $class = $matches[self::CLASS_KEY];
        if (!$this->reflectionProvider->hasClass($class)) {
            return null;
        }
        $classReflection = $this->reflectionProvider->getClass($class);
        $method = $matches[self::METHOD_KEY];
        if (!$classReflection->hasMethod($method)) {
            return null;
        }
        return $classReflection->getMethod($method, $scope);
    }
}
