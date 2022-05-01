<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix20220501\Nette\Utils;

use RectorPrefix20220501\Nette;
/**
 * PHP type reflection.
 */
final class Type
{
    /** @var array */
    private $types;
    /** @var bool */
    private $single;
    /** @var string  |, & */
    private $kind;
    /**
     * Creates a Type object based on reflection. Resolves self, static and parent to the actual class name.
     * If the subject has no type, it returns null.
     * @param  \ReflectionFunctionAbstract|\ReflectionParameter|\ReflectionProperty  $reflection
     */
    public static function fromReflection($reflection) : ?self
    {
        if ($reflection instanceof \ReflectionProperty && \PHP_VERSION_ID < 70400) {
            return null;
        } elseif ($reflection instanceof \ReflectionMethod) {
            $type = $reflection->getReturnType() ?? (\PHP_VERSION_ID >= 80100 ? $reflection->getTentativeReturnType() : null);
        } else {
            $type = $reflection instanceof \ReflectionFunctionAbstract ? $reflection->getReturnType() : $reflection->getType();
        }
        if ($type === null) {
            return null;
        } elseif ($type instanceof \ReflectionNamedType) {
            $name = self::resolve($type->getName(), $reflection);
            return new self($type->allowsNull() && $type->getName() !== 'mixed' ? [$name, 'null'] : [$name]);
        } elseif ($type instanceof \ReflectionUnionType || $type instanceof \RectorPrefix20220501\ReflectionIntersectionType) {
            return new self(\array_map(function ($t) use($reflection) {
                return self::resolve($t->getName(), $reflection);
            }, $type->getTypes()), $type instanceof \ReflectionUnionType ? '|' : '&');
        } else {
            throw new \RectorPrefix20220501\Nette\InvalidStateException('Unexpected type of ' . \RectorPrefix20220501\Nette\Utils\Reflection::toString($reflection));
        }
    }
    /**
     * Creates the Type object according to the text notation.
     */
    public static function fromString(string $type) : self
    {
        if (!\preg_match('#(?:
			\\?([\\w\\\\]+)|
			[\\w\\\\]+ (?: (&[\\w\\\\]+)* | (\\|[\\w\\\\]+)* )
		)()$#xAD', $type, $m)) {
            throw new \RectorPrefix20220501\Nette\InvalidArgumentException("Invalid type '{$type}'.");
        }
        [, $nType, $iType] = $m;
        if ($nType) {
            return new self([$nType, 'null']);
        } elseif ($iType) {
            return new self(\explode('&', $type), '&');
        } else {
            return new self(\explode('|', $type));
        }
    }
    /**
     * Resolves 'self', 'static' and 'parent' to the actual class name.
     * @param  \ReflectionFunctionAbstract|\ReflectionParameter|\ReflectionProperty  $reflection
     */
    public static function resolve(string $type, $reflection) : string
    {
        $lower = \strtolower($type);
        if ($reflection instanceof \ReflectionFunction) {
            return $type;
        } elseif ($lower === 'self' || $lower === 'static') {
            return $reflection->getDeclaringClass()->name;
        } elseif ($lower === 'parent' && $reflection->getDeclaringClass()->getParentClass()) {
            return $reflection->getDeclaringClass()->getParentClass()->name;
        } else {
            return $type;
        }
    }
    private function __construct(array $types, string $kind = '|')
    {
        if ($types[0] === 'null') {
            // null as last
            \array_push($types, \array_shift($types));
        }
        $this->types = $types;
        $this->single = ($types[1] ?? 'null') === 'null';
        $this->kind = \count($types) > 1 ? $kind : '';
    }
    public function __toString() : string
    {
        return $this->single ? (\count($this->types) > 1 ? '?' : '') . $this->types[0] : \implode($this->kind, $this->types);
    }
    /**
     * Returns the array of subtypes that make up the compound type as strings.
     * @return string[]
     */
    public function getNames() : array
    {
        return $this->types;
    }
    /**
     * Returns the array of subtypes that make up the compound type as Type objects:
     * @return self[]
     */
    public function getTypes() : array
    {
        return \array_map(function ($name) {
            return self::fromString($name);
        }, $this->types);
    }
    /**
     * Returns the type name for single types, otherwise null.
     */
    public function getSingleName() : ?string
    {
        return $this->single ? $this->types[0] : null;
    }
    /**
     * Returns true whether it is a union type.
     */
    public function isUnion() : bool
    {
        return $this->kind === '|';
    }
    /**
     * Returns true whether it is an intersection type.
     */
    public function isIntersection() : bool
    {
        return $this->kind === '&';
    }
    /**
     * Returns true whether it is a single type. Simple nullable types are also considered to be single types.
     */
    public function isSingle() : bool
    {
        return $this->single;
    }
    /**
     * Returns true whether the type is both a single and a PHP built-in type.
     */
    public function isBuiltin() : bool
    {
        return $this->single && \RectorPrefix20220501\Nette\Utils\Reflection::isBuiltinType($this->types[0]);
    }
    /**
     * Returns true whether the type is both a single and a class name.
     */
    public function isClass() : bool
    {
        return $this->single && !\RectorPrefix20220501\Nette\Utils\Reflection::isBuiltinType($this->types[0]);
    }
    /**
     * Determines if type is special class name self/parent/static.
     */
    public function isClassKeyword() : bool
    {
        return $this->single && \RectorPrefix20220501\Nette\Utils\Reflection::isClassKeyword($this->types[0]);
    }
    /**
     * Verifies type compatibility. For example, it checks if a value of a certain type could be passed as a parameter.
     */
    public function allows(string $type) : bool
    {
        if ($this->types === ['mixed']) {
            return \true;
        }
        $type = self::fromString($type);
        if ($this->isIntersection()) {
            if (!$type->isIntersection()) {
                return \false;
            }
            return \RectorPrefix20220501\Nette\Utils\Arrays::every($this->types, function ($currentType) use($type) {
                $builtin = \RectorPrefix20220501\Nette\Utils\Reflection::isBuiltinType($currentType);
                return \RectorPrefix20220501\Nette\Utils\Arrays::some($type->types, function ($testedType) use($currentType, $builtin) {
                    return $builtin ? \strcasecmp($currentType, $testedType) === 0 : \is_a($testedType, $currentType, \true);
                });
            });
        }
        $method = $type->isIntersection() ? 'some' : 'every';
        return \RectorPrefix20220501\Nette\Utils\Arrays::$method($type->types, function ($testedType) {
            $builtin = \RectorPrefix20220501\Nette\Utils\Reflection::isBuiltinType($testedType);
            return \RectorPrefix20220501\Nette\Utils\Arrays::some($this->types, function ($currentType) use($testedType, $builtin) {
                return $builtin ? \strcasecmp($currentType, $testedType) === 0 : \is_a($testedType, $currentType, \true);
            });
        });
    }
}
