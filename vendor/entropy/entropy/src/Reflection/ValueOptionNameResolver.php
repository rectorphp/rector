<?php

declare (strict_types=1);
namespace RectorPrefix202610\Entropy\Reflection;

use RectorPrefix202610\Entropy\Attribute\RelatedTest;
use RectorPrefix202610\Entropy\Tests\Reflection\ValueOptionNameResolver\ValueOptionNameResolverTest;
use ReflectionMethod;
use ReflectionNamedType;
final class ValueOptionNameResolver
{
    /**
     * Option names that carry a value, so the parser knows which "--name"
     * consumes the next token. Every non-bool run() parameter takes a value;
     * a bool parameter is a flag and never does.
     *
     * @return array<string, true> map: optionName => true
     */
    public static function resolve(ReflectionMethod $reflectionMethod): array
    {
        $valueOptionNames = [];
        foreach ($reflectionMethod->getParameters() as $key => $reflectionParameter) {
            $type = $reflectionParameter->getType();
            // flags take no value
            if ($type instanceof ReflectionNamedType && $type->getName() === 'bool') {
                continue;
            }
            $optionName = strtolower((string) preg_replace('/[A-Z]/', '-$0', $reflectionParameter->getName()));
            // plural array option mapped to singular --option (mirrors the mapper)
            $isArray = $type instanceof ReflectionNamedType && $type->getName() === 'array' && !$reflectionParameter->isVariadic();
            if ($key !== 0 && $isArray && substr_compare($optionName, 's', -strlen('s')) === 0) {
                // cast for PHP 7.4, where substr() is typed string|false
                $optionName = (string) substr($optionName, 0, -1);
            }
            $valueOptionNames[$optionName] = \true;
        }
        return $valueOptionNames;
    }
}
