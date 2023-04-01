<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix202304\Nette\Utils;

use RectorPrefix202304\Nette;
/**
 * PHP reflection helpers.
 */
final class Reflection
{
    use Nette\StaticClass;
    /**
     * Determines if type is PHP built-in type. Otherwise, it is the class name.
     */
    public static function isBuiltinType(string $type) : bool
    {
        return Validators::isBuiltinType($type);
    }
    /**
     * Determines if type is special class name self/parent/static.
     */
    public static function isClassKeyword(string $name) : bool
    {
        return Validators::isClassKeyword($name);
    }
    /**
     * Returns the type of return value of given function or method and normalizes `self`, `static`, and `parent` to actual class names.
     * If the function does not have a return type, it returns null.
     * If the function has union or intersection type, it throws Nette\InvalidStateException.
     */
    public static function getReturnType(\ReflectionFunctionAbstract $func) : ?string
    {
        $type = $func->getReturnType() ?? (\PHP_VERSION_ID >= 80100 && $func instanceof \ReflectionMethod ? $func->getTentativeReturnType() : null);
        return self::getType($func, $type);
    }
    /**
     * @deprecated
     */
    public static function getReturnTypes(\ReflectionFunctionAbstract $func) : array
    {
        $type = Type::fromReflection($func);
        return $type ? $type->getNames() : [];
    }
    /**
     * Returns the type of given parameter and normalizes `self` and `parent` to the actual class names.
     * If the parameter does not have a type, it returns null.
     * If the parameter has union or intersection type, it throws Nette\InvalidStateException.
     */
    public static function getParameterType(\ReflectionParameter $param) : ?string
    {
        return self::getType($param, $param->getType());
    }
    /**
     * @deprecated
     */
    public static function getParameterTypes(\ReflectionParameter $param) : array
    {
        $type = Type::fromReflection($param);
        return $type ? $type->getNames() : [];
    }
    /**
     * Returns the type of given property and normalizes `self` and `parent` to the actual class names.
     * If the property does not have a type, it returns null.
     * If the property has union or intersection type, it throws Nette\InvalidStateException.
     */
    public static function getPropertyType(\ReflectionProperty $prop) : ?string
    {
        return self::getType($prop, \PHP_VERSION_ID >= 70400 ? \method_exists($prop, 'getType') ? $prop->getType() : null : null);
    }
    /**
     * @deprecated
     */
    public static function getPropertyTypes(\ReflectionProperty $prop) : array
    {
        $type = Type::fromReflection($prop);
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
            return Type::resolve($type->getName(), $reflection);
        } elseif ($type instanceof \ReflectionUnionType || $type instanceof \ReflectionIntersectionType) {
            throw new Nette\InvalidStateException('The ' . self::toString($reflection) . ' is not expected to have a union or intersection type.');
        } else {
            throw new Nette\InvalidStateException('Unexpected type of ' . self::toString($reflection));
        }
    }
    /**
     * Returns the default value of parameter. If it is a constant, it returns its value.
     * @return mixed
     * @throws \ReflectionException  If the parameter does not have a default value or the constant cannot be resolved
     */
    public static function getParameterDefaultValue(\ReflectionParameter $param)
    {
        if ($param->isDefaultValueConstant()) {
            $const = $orig = $param->getDefaultValueConstantName();
            $pair = \explode('::', $const);
            if (isset($pair[1])) {
                $pair[0] = Type::resolve($pair[0], $param);
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
     */
    public static function getPropertyDeclaringClass(\ReflectionProperty $prop) : \ReflectionClass
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
     */
    public static function getMethodDeclaringMethod(\ReflectionMethod $method) : \ReflectionMethod
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
    public static function toString(\Reflector $ref) : string
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
            throw new Nette\InvalidArgumentException();
        }
    }
    /**
     * Expands the name of the class to full name in the given context of given class.
     * Thus, it returns how the PHP parser would understand $name if it were written in the body of the class $context.
     * @throws Nette\InvalidArgumentException
     */
    public static function expandClassName(string $name, \ReflectionClass $context) : string
    {
        $lower = \strtolower($name);
        if (empty($name)) {
            throw new Nette\InvalidArgumentException('Class name must not be empty.');
        } elseif (Validators::isBuiltinType($lower)) {
            return $lower;
        } elseif ($lower === 'self' || $lower === 'static') {
            return $context->name;
        } elseif ($lower === 'parent') {
            return $context->getParentClass() ? $context->getParentClass()->name : 'parent';
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
    /** @return array of [alias => class] */
    public static function getUseStatements(\ReflectionClass $class) : array
    {
        if ($class->isAnonymous()) {
            throw new Nette\NotImplementedException('Anonymous classes are not supported.');
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
    private static function parseUseStatements(string $code, ?string $forClass = null) : array
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
                case \PHP_VERSION_ID < 80100 ? \T_CLASS : \T_ENUM:
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
