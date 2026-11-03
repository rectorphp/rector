<?php

declare (strict_types=1);
namespace Rector\PhpAttribute\NodeFactory;

use PhpParser\Node\Arg;
use PhpParser\Node\ArrayItem;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\String_;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\ArrayType;
use PHPStan\Type\MixedType;
/**
 * Annotation arguments become named attribute arguments. A named argument that the attribute constructor does not
 * declare is a runtime error ("Unknown named parameter"), while the annotation form accepted it, e.g. Symfony validator
 * constraints that take extra settings through an $options array.
 *
 * When the constructor has no parameter of that name but does have an array-accepting $options parameter, the unknown
 * arguments are folded into it. In every other case the arguments are left untouched.
 */
final class UnknownNamedArgsToOptionsFolder
{
    /**
     * @readonly
     */
    private ReflectionProvider $reflectionProvider;
    /**
     * @var string
     */
    private const OPTIONS_PARAMETER = 'options';
    public function __construct(ReflectionProvider $reflectionProvider)
    {
        $this->reflectionProvider = $reflectionProvider;
    }
    /**
     * @param list<Arg> $args
     * @return list<Arg>
     */
    public function fold(array $args, string $attributeClass): array
    {
        $constructorParameterNames = $this->resolveConstructorParameterNames($attributeClass);
        if ($constructorParameterNames === null) {
            return $args;
        }
        $keptArgs = [];
        $optionItems = [];
        $explicitOptionsArg = null;
        foreach ($args as $arg) {
            if (!$arg->name instanceof Identifier) {
                $keptArgs[] = $arg;
                continue;
            }
            $name = $arg->name->toString();
            if ($name === self::OPTIONS_PARAMETER) {
                $explicitOptionsArg = $arg;
                continue;
            }
            if (in_array($name, $constructorParameterNames, \true)) {
                $keptArgs[] = $arg;
                continue;
            }
            $optionItems[] = new ArrayItem($arg->value, new String_($name));
        }
        if ($optionItems === []) {
            return $args;
        }
        if ($explicitOptionsArg instanceof Arg) {
            // an explicit options: ... of an unexpected shape cannot be merged safely, keep the original arguments
            if (!$explicitOptionsArg->value instanceof Array_) {
                return $args;
            }
            $optionItems = array_merge($explicitOptionsArg->value->items, $optionItems);
        }
        $keptArgs[] = new Arg(new Array_($optionItems), \false, \false, [], new Identifier(self::OPTIONS_PARAMETER));
        return $keptArgs;
    }
    /**
     * @return string[]|null null when the attribute cannot be folded: class or constructor unknown, variadic, or no
     *                       array-accepting $options parameter
     */
    private function resolveConstructorParameterNames(string $attributeClass): ?array
    {
        if (!$this->reflectionProvider->hasClass($attributeClass)) {
            return null;
        }
        $classReflection = $this->reflectionProvider->getClass($attributeClass);
        if (!$classReflection->hasConstructor()) {
            return null;
        }
        $variants = $classReflection->getConstructor()->getVariants();
        if (count($variants) !== 1) {
            return null;
        }
        $parameterNames = [];
        $hasOptionsParameter = \false;
        foreach ($variants[0]->getParameters() as $parameterReflection) {
            if ($parameterReflection->isVariadic()) {
                return null;
            }
            $parameterNames[] = $parameterReflection->getName();
            if ($parameterReflection->getName() === self::OPTIONS_PARAMETER) {
                $arrayType = new ArrayType(new MixedType(), new MixedType());
                $hasOptionsParameter = $parameterReflection->getType()->accepts($arrayType, \true)->yes();
            }
        }
        return $hasOptionsParameter ? $parameterNames : null;
    }
}
