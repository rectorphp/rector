<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix20211020\Nette\Utils;

use RectorPrefix20211020\Nette;
/**
 * PHP reflection helpers.
 */
final class Reflection
{
    use Nette\StaticClass;
    private const BUILTIN_TYPES = ['string' => 1, 'int' => 1, 'float' => 1, 'bool' => 1, 'array' => 1, 'object' => 1, 'callable' => 1, 'iterable' => 1, 'void' => 1, 'null' => 1, 'mixed' => 1, 'false' => 1, 'never' => 1];
    /**
     * Determines if type is PHP built-in type. Otherwise, it is the class name.
     * @param string $type
     */
    public static function isBuiltinType($type) : bool
    {
        return isset(self::BUILTIN_TYPES[\strtolower($type)]);
    }
    /**
     * Returns the type of return value of given function or method and normalizes `self`, `static`, and `parent` to actual class names.
     * If the function does not have a return type, it returns null.
     * If the function has union or intersection type, it throws Nette\InvalidStateException.
     * @param \ReflectionFunctionAbstract $func
     */
    public static function getReturnType($func) : ?string
    {
        $type = $func->getReturnType() ?? (\PHP_VERSION_ID >= 80100 && $func instanceof \ReflectionMethod ? $func->getTentativeReturnType() : null);
        return self::getType($func, $type);
    }
    /**
     * @deprecated
     * @param \ReflectionFunctionAbstract $func
     */
    public static function getReturnTypes($func) : array
    {
        $type = \RectorPrefix20211020\Nette\Utils\Type::fromReflection($func);
        return $type ? $type->getNames() : [];
    }
    /**
     * Returns the type of given parameter and normalizes `self` and `parent` to the actual class names.
     * If the parameter does not have a type, it returns null.
     * If the parameter has union or intersection type, it throws Nette\InvalidStateException.
     * @param \ReflectionParameter $param
     */
    public static function getParameterType($param) : ?string
    {
        return self::getType($param, $param->getType());
    }
    /**
     * @deprecated
     * @param \ReflectionParameter $param
     */
    public static function getParameterTypes($param) : array
    {
        $type = \RectorPrefix20211020\Nette\Utils\Type::fromReflection($param);
        return $type ? $type->getNames() : [];
    }
    /**
     * Returns the type of given property and normalizes `self` and `parent` to the actual class names.
     * If the property does not have a type, it returns null.
     * If the property has union or intersection type, it throws Nette\InvalidStateException.
     * @param \ReflectionProperty $prop
     */
    public static function getPropertyType($prop) : ?string
    {
        return self::getType($prop, \PHP_VERSION_ID >= 70400 ? $prop->getType() : null);
    }
    /**
     * @deprecated
     * @param \ReflectionProperty $prop
     */
    public static function getPropertyTypes($prop) : array
    {
        $type = \RectorPrefix20211020\Nette\Utils\Type::fromReflection($prop);
        return $type ? $type->getNames() : [];
    }
    /**
     * @param  \ReflectionFunction|\ReflectionMethod|\ReflectionParameter|\ReflectionProperty  $reflection
     */
    private static function getType($reflection, ?\ReflectionType $type) : ?string
    {
        if ($type === null) {
            return null;
        } elseif ($type instanceof \ReflectionNamedType) {
            return \RectorPrefix20211020\Nette\Utils\Type::resolve($type->getName(), $reflection);
        } elseif ($type instanceof \ReflectionUnionType || $type instanceof \RectorPrefix20211020\ReflectionIntersectionType) {
            throw new \RectorPrefix20211020\Nette\InvalidStateException('The ' . self::toString($reflection) . ' is not expected to have a union or intersection type.');
        } else {
            throw new \RectorPrefix20211020\Nette\InvalidStateException('Unexpected type of ' . self::toString($reflection));
        }
    }
    /**
     * Returns the default value of parameter. If it is a constant, it returns its value.
     * @return mixed
     * @throws \ReflectionException  If the parameter does not have a default value or the constant cannot be resolved
     * @param \ReflectionParameter $param
     */
    public static function getParameterDefaultValue($param)
    {
        if ($param->isDefaultValueConstant()) {
            $const = $orig = $param->getDefaultValueConstantName();
            $pair = \explode('::', $const);
            if (isset($pair[1])) {
                $pair[0] = \RectorPrefix20211020\Nette\Utils\Type::resolve($pair[0], $param);
                try {
                    $rcc = new \ReflectionClassConstant($pair[0], $pair[1]);
                } catch (\ReflectionException $e) {
                    $name = self::toString($param);
                    throw new \ReflectionException("Unable to resolve constant {$orig} used as default value of {$name}.", 0, $e);
                }
                return $rcc->getValue();
            } elseif (!\defined($const)) {
                $const = \substr((string) \strrchr($const, '\\'), 1);
                if (!\defined($const)) {
                    $name = self::toString($param);
                    throw new \ReflectionException("Unable to resolve constant {$orig} used as default value of {$name}.");
                }
            }
            return \constant($const);
        }
        return $param->getDefaultValue();
    }
    /**
     * Returns a reflection of a class or trait that contains a declaration of given property. Property can also be declared in the trait.
     * @param \ReflectionProperty $prop
     */
    public static function getPropertyDeclaringClass($prop) : \ReflectionClass
    {
        foreach ($prop->getDeclaringClass()->getTraits() as $trait) {
            if ($trait->hasProperty($prop->name) && $trait->getProperty($prop->name)->getDocComment() === $prop->getDocComment()) {
                return self::getPropertyDeclaringClass($trait->getProperty($prop->name));
            }
        }
        return $prop->getDeclaringClass();
    }
    /**
     * Returns a reflection of a method that contains a declaration of $method.
     * Usually, each method is its own declaration, but the body of the method can also be in the trait and under a different name.
     * @param \ReflectionMethod $method
     */
    public static function getMethodDeclaringMethod($method) : \ReflectionMethod
    {
        // file & line guessing as workaround for insufficient PHP reflection
        $decl = $method->getDeclaringClass();
        if ($decl->getFileName() === $method->getFileName() && $decl->getStartLine() <= $method->getStartLine() && $decl->getEndLine() >= $method->getEndLine()) {
            return $method;
        }
        $hash = [$method->getFileName(), $method->getStartLine(), $method->getEndLine()];
        if (($alias = $decl->getTraitAliases()[$method->name] ?? null) && ($m = new \ReflectionMethod($alias)) && $hash === [$m->getFileName(), $m->getStartLine(), $m->getEndLine()]) {
            return self::getMethodDeclaringMethod($m);
        }
        foreach ($decl->getTraits() as $trait) {
            if ($trait->hasMethod($method->name) && ($m = $trait->getMethod($method->name)) && $hash === [$m->getFileName(), $m->getStartLine(), $m->getEndLine()]) {
                return self::getMethodDeclaringMethod($m);
            }
        }
        return $method;
    }
    /**
     * Finds out if reflection has access to PHPdoc comments. Comments may not be available due to the opcode cache.
     */
    public static function areCommentsAvailable() : bool
    {
        static $res;
        return $res ?? ($res = (bool) (new \ReflectionMethod(__METHOD__))->getDocComment());
    }
    /**
     * @param \Reflector $ref
     */
    public static function toString($ref) : string
    {
        if ($ref instanceof \ReflectionClass) {
            return $ref->name;
        } elseif ($ref instanceof \ReflectionMethod) {
            return $ref->getDeclaringClass()->name . '::' . $ref->name . '()';
        } elseif ($ref instanceof \ReflectionFunction) {
            return $ref->name . '()';
        } elseif ($ref instanceof \ReflectionProperty) {
            return self::getPropertyDeclaringClass($ref)->name . '::$' . $ref->name;
        } elseif ($ref instanceof \ReflectionParameter) {
            return '$' . $ref->name . ' in ' . self::toString($ref->getDeclaringFunction());
        } else {
            throw new \RectorPrefix20211020\Nette\InvalidArgumentException();
        }
    }
    /**
     * Expands the name of the class to full name in the given context of given class.
     * Thus, it returns how the PHP parser would understand $name if it were written in the body of the class $context.
     * @throws Nette\InvalidArgumentException
     * @param string $name
     * @param \ReflectionClass $context
     */
    public static function expandClassName($name, $context) : string
    {
        $lower = \strtolower($name);
        if (empty($name)) {
            throw new \RectorPrefix20211020\Nette\InvalidArgumentException('Class name must not be empty.');
        } elseif (isset(self::BUILTIN_TYPES[$lower])) {
            return $lower;
        } elseif ($lower === 'self' || $lower === 'static') {
            return $context->name;
        } elseif ($name[0] === '\\') {
            // fully qualified name
            return \ltrim($name, '\\');
        }
        $uses = self::getUseStatements($context);
        $parts = \explode('\\', $name, 2);
        if (isset($uses[$parts[0]])) {
            $parts[0] = $uses[$parts[0]];
            return \implode('\\', $parts);
        } elseif ($context->inNamespace()) {
            return $context->getNamespaceName() . '\\' . $name;
        } else {
            return $name;
        }
    }
    /** @return array of [alias => class]
     * @param \ReflectionClass $class */
    public static function getUseStatements($class) : array
    {
        if ($class->isAnonymous()) {
            throw new \RectorPrefix20211020\Nette\NotImplementedException('Anonymous classes are not supported.');
        }
        static $cache = [];
        if (!isset($cache[$name = $class->name])) {
            if ($class->isInternal()) {
                $cache[$name] = [];
            } else {
                $code = \file_get_contents($class->getFileName());
                $cache = self::parseUseStatements($code, $name) + $cache;
            }
        }
        return $cache[$name];
    }
    /**
     * Parses PHP code to [class => [alias => class, ...]]
     */
    private static function parseUseStatements(string $code, string $forClass = null) : array
    {
        try {
            $tokens = \token_get_all($code, \TOKEN_PARSE);
        } catch (\ParseError $e) {
            \trigger_error($e->getMessage(), \E_USER_NOTICE);
            $tokens = [];
        }
        $namespace = $class = $classLevel = $level = null;
        $res = $uses = [];
        $nameTokens = \PHP_VERSION_ID < 80000 ? [\T_STRING, \T_NS_SEPARATOR] : [\T_STRING, \T_NS_SEPARATOR, \T_NAME_QUALIFIED, \T_NAME_FULLY_QUALIFIED];
        while ($token = \current($tokens)) {
            \next($tokens);
            switch (\is_array($token) ? $token[0] : $token) {
                case \T_NAMESPACE:
                    $namespace = \ltrim(self::fetch($tokens, $nameTokens) . '\\', '\\');
                    $uses = [];
                    break;
                case \T_CLASS:
                case \T_INTERFACE:
                case \T_TRAIT:
                case \PHP_VERSION_ID < 80100 ? \T_CLASS : T_ENUM:
                    if ($name = self::fetch($tokens, \T_STRING)) {
                        $class = $namespace . $name;
                        $classLevel = $level + 1;
                        $res[$class] = $uses;
                        if ($class === $forClass) {
                            return $res;
                        }
                    }
                    break;
                case \T_USE:
                    while (!$class && ($name = self::fetch($tokens, $nameTokens))) {
                        $name = \ltrim($name, '\\');
                        if (self::fetch($tokens, '{')) {
                            while ($suffix = self::fetch($tokens, $nameTokens)) {
                                if (self::fetch($tokens, \T_AS)) {
                                    $uses[self::fetch($tokens, \T_STRING)] = $name . $suffix;
                                } else {
                                    $tmp = \explode('\\', $suffix);
                                    $uses[\end($tmp)] = $name . $suffix;
                                }
                                if (!self::fetch($tokens, ',')) {
                                    break;
                                }
                            }
                        } elseif (self::fetch($tokens, \T_AS)) {
                            $uses[self::fetch($tokens, \T_STRING)] = $name;
                        } else {
                            $tmp = \explode('\\', $name);
                            $uses[\end($tmp)] = $name;
                        }
                        if (!self::fetch($tokens, ',')) {
                            break;
                        }
                    }
                    break;
                case \T_CURLY_OPEN:
                case \T_DOLLAR_OPEN_CURLY_BRACES:
                case '{':
                    $level++;
                    break;
                case '}':
                    if ($level === $classLevel) {
                        $class = $classLevel = null;
                    }
                    $level--;
            }
        }
        return $res;
    }
    private static function fetch(array &$tokens, $take) : ?string
    {
        $res = null;
        while ($token = \current($tokens)) {
            [$token, $s] = \is_array($token) ? $token : [$token, $token];
            if (\in_array($token, (array) $take, \true)) {
                $res .= $s;
            } elseif (!\in_array($token, [\T_DOC_COMMENT, \T_WHITESPACE, \T_COMMENT], \true)) {
                break;
            }
            \next($tokens);
        }
        return $res;
    }
}
