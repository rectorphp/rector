<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace RectorPrefix20211020\Nette\Utils;

use RectorPrefix20211020\Nette;
use RectorPrefix20211020\Nette\MemberAccessException;
/**
 * Nette\SmartObject helpers.
 */
final class ObjectHelpers
{
    use Nette\StaticClass;
    /** @throws MemberAccessException
     * @param string $class
     * @param string $name */
    public static function strictGet($class, $name) : void
    {
        $rc = new \ReflectionClass($class);
        $hint = self::getSuggestion(\array_merge(\array_filter($rc->getProperties(\ReflectionProperty::IS_PUBLIC), function ($p) {
            return !$p->isStatic();
        }), self::parseFullDoc($rc, '~^[ \\t*]*@property(?:-read)?[ \\t]+(?:\\S+[ \\t]+)??\\$(\\w+)~m')), $name);
        throw new \RectorPrefix20211020\Nette\MemberAccessException("Cannot read an undeclared property {$class}::\${$name}" . ($hint ? ", did you mean \${$hint}?" : '.'));
    }
    /** @throws MemberAccessException
     * @param string $class
     * @param string $name */
    public static function strictSet($class, $name) : void
    {
        $rc = new \ReflectionClass($class);
        $hint = self::getSuggestion(\array_merge(\array_filter($rc->getProperties(\ReflectionProperty::IS_PUBLIC), function ($p) {
            return !$p->isStatic();
        }), self::parseFullDoc($rc, '~^[ \\t*]*@property(?:-write)?[ \\t]+(?:\\S+[ \\t]+)??\\$(\\w+)~m')), $name);
        throw new \RectorPrefix20211020\Nette\MemberAccessException("Cannot write to an undeclared property {$class}::\${$name}" . ($hint ? ", did you mean \${$hint}?" : '.'));
    }
    /** @throws MemberAccessException
     * @param string $class
     * @param string $method
     * @param mixed[] $additionalMethods */
    public static function strictCall($class, $method, $additionalMethods = []) : void
    {
        $trace = \debug_backtrace(0, 3);
        // suppose this method is called from __call()
        $context = ($trace[1]['function'] ?? null) === '__call' ? $trace[2]['class'] ?? null : null;
        if ($context && \is_a($class, $context, \true) && \method_exists($context, $method)) {
            // called parent::$method()
            $class = \get_parent_class($context);
        }
        if (\method_exists($class, $method)) {
            // insufficient visibility
            $rm = new \ReflectionMethod($class, $method);
            $visibility = $rm->isPrivate() ? 'private ' : ($rm->isProtected() ? 'protected ' : '');
            throw new \RectorPrefix20211020\Nette\MemberAccessException("Call to {$visibility}method {$class}::{$method}() from " . ($context ? "scope {$context}." : 'global scope.'));
        } else {
            $hint = self::getSuggestion(\array_merge(\get_class_methods($class), self::parseFullDoc(new \ReflectionClass($class), '~^[ \\t*]*@method[ \\t]+(?:\\S+[ \\t]+)??(\\w+)\\(~m'), $additionalMethods), $method);
            throw new \RectorPrefix20211020\Nette\MemberAccessException("Call to undefined method {$class}::{$method}()" . ($hint ? ", did you mean {$hint}()?" : '.'));
        }
    }
    /** @throws MemberAccessException
     * @param string $class
     * @param string $method */
    public static function strictStaticCall($class, $method) : void
    {
        $trace = \debug_backtrace(0, 3);
        // suppose this method is called from __callStatic()
        $context = ($trace[1]['function'] ?? null) === '__callStatic' ? $trace[2]['class'] ?? null : null;
        if ($context && \is_a($class, $context, \true) && \method_exists($context, $method)) {
            // called parent::$method()
            $class = \get_parent_class($context);
        }
        if (\method_exists($class, $method)) {
            // insufficient visibility
            $rm = new \ReflectionMethod($class, $method);
            $visibility = $rm->isPrivate() ? 'private ' : ($rm->isProtected() ? 'protected ' : '');
            throw new \RectorPrefix20211020\Nette\MemberAccessException("Call to {$visibility}method {$class}::{$method}() from " . ($context ? "scope {$context}." : 'global scope.'));
        } else {
            $hint = self::getSuggestion(\array_filter((new \ReflectionClass($class))->getMethods(\ReflectionMethod::IS_PUBLIC), function ($m) {
                return $m->isStatic();
            }), $method);
            throw new \RectorPrefix20211020\Nette\MemberAccessException("Call to undefined static method {$class}::{$method}()" . ($hint ? ", did you mean {$hint}()?" : '.'));
        }
    }
    /**
     * Returns array of magic properties defined by annotation @property.
     * @return array of [name => bit mask]
     * @internal
     * @param string $class
     */
    public static function getMagicProperties($class) : array
    {
        static $cache;
        $props =& $cache[$class];
        if ($props !== null) {
            return $props;
        }
        $rc = new \ReflectionClass($class);
        \preg_match_all('~^  [ \\t*]*  @property(|-read|-write)  [ \\t]+  [^\\s$]+  [ \\t]+  \\$  (\\w+)  ()~mx', (string) $rc->getDocComment(), $matches, \PREG_SET_ORDER);
        $props = [];
        foreach ($matches as [, $type, $name]) {
            $uname = \ucfirst($name);
            $write = $type !== '-read' && $rc->hasMethod($nm = 'set' . $uname) && ($rm = $rc->getMethod($nm))->name === $nm && !$rm->isPrivate() && !$rm->isStatic();
            $read = $type !== '-write' && ($rc->hasMethod($nm = 'get' . $uname) || $rc->hasMethod($nm = 'is' . $uname)) && ($rm = $rc->getMethod($nm))->name === $nm && !$rm->isPrivate() && !$rm->isStatic();
            if ($read || $write) {
                $props[$name] = $read << 0 | ($nm[0] === 'g') << 1 | $rm->returnsReference() << 2 | $write << 3;
            }
        }
        foreach ($rc->getTraits() as $trait) {
            $props += self::getMagicProperties($trait->name);
        }
        if ($parent = \get_parent_class($class)) {
            $props += self::getMagicProperties($parent);
        }
        return $props;
    }
    /**
     * Finds the best suggestion (for 8-bit encoding).
     * @param  (\ReflectionFunctionAbstract|\ReflectionParameter|\ReflectionClass|\ReflectionProperty|string)[]  $possibilities
     * @internal
     * @param string $value
     */
    public static function getSuggestion($possibilities, $value) : ?string
    {
        $norm = \preg_replace($re = '#^(get|set|has|is|add)(?=[A-Z])#', '+', $value);
        $best = null;
        $min = (\strlen($value) / 4 + 1) * 10 + 0.1;
        foreach (\array_unique($possibilities, \SORT_REGULAR) as $item) {
            $item = $item instanceof \Reflector ? $item->name : $item;
            if ($item !== $value && (($len = \levenshtein($item, $value, 10, 11, 10)) < $min || ($len = \levenshtein(\preg_replace($re, '*', $item), $norm, 10, 11, 10)) < $min)) {
                $min = $len;
                $best = $item;
            }
        }
        return $best;
    }
    private static function parseFullDoc(\ReflectionClass $rc, string $pattern) : array
    {
        do {
            $doc[] = $rc->getDocComment();
            $traits = $rc->getTraits();
            while ($trait = \array_pop($traits)) {
                $doc[] = $trait->getDocComment();
                $traits += $trait->getTraits();
            }
        } while ($rc = $rc->getParentClass());
        return \preg_match_all($pattern, \implode($doc), $m) ? $m[1] : [];
    }
    /**
     * Checks if the public non-static property exists.
     * @return bool|string returns 'event' if the property exists and has event like name
     * @internal
     * @param string $class
     * @param string $name
     */
    public static function hasProperty($class, $name)
    {
        static $cache;
        $prop =& $cache[$class][$name];
        if ($prop === null) {
            $prop = \false;
            try {
                $rp = new \ReflectionProperty($class, $name);
                if ($rp->isPublic() && !$rp->isStatic()) {
                    $prop = $name >= 'onA' && $name < 'on_' ? 'event' : \true;
                }
            } catch (\ReflectionException $e) {
            }
        }
        return $prop;
    }
}
