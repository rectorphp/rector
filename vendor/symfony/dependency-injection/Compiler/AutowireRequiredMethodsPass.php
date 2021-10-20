<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20211020\Symfony\Component\DependencyInjection\Compiler;

use RectorPrefix20211020\Symfony\Component\DependencyInjection\Definition;
use RectorPrefix20211020\Symfony\Contracts\Service\Attribute\Required;
/**
 * Looks for definitions with autowiring enabled and registers their corresponding "@required" methods as setters.
 *
 * @author Nicolas Grekas <p@tchwork.com>
 */
class AutowireRequiredMethodsPass extends \RectorPrefix20211020\Symfony\Component\DependencyInjection\Compiler\AbstractRecursivePass
{
    /**
     * {@inheritdoc}
     * @param bool $isRoot
     */
    protected function processValue($value, $isRoot = \false)
    {
        $value = parent::processValue($value, $isRoot);
        if (!$value instanceof \RectorPrefix20211020\Symfony\Component\DependencyInjection\Definition || !$value->isAutowired() || $value->isAbstract() || !$value->getClass()) {
            return $value;
        }
        if (!($reflectionClass = $this->container->getReflectionClass($value->getClass(), \false))) {
            return $value;
        }
        $alreadyCalledMethods = [];
        $withers = [];
        foreach ($value->getMethodCalls() as [$method]) {
            $alreadyCalledMethods[\strtolower($method)] = \true;
        }
        foreach ($reflectionClass->getMethods() as $reflectionMethod) {
            $r = $reflectionMethod;
            if ($r->isConstructor() || isset($alreadyCalledMethods[\strtolower($r->name)])) {
                continue;
            }
            while (\true) {
                if (\PHP_VERSION_ID >= 80000 && $r->getAttributes(\RectorPrefix20211020\Symfony\Contracts\Service\Attribute\Required::class)) {
                    if ($this->isWither($r, $r->getDocComment() ?: '')) {
                        $withers[] = [$r->name, [], \true];
                    } else {
                        $value->addMethodCall($r->name, []);
                    }
                    break;
                }
                if (\false !== ($doc = $r->getDocComment())) {
                    if (\false !== \stripos($doc, '@required') && \preg_match('#(?:^/\\*\\*|\\n\\s*+\\*)\\s*+@required(?:\\s|\\*/$)#i', $doc)) {
                        if ($this->isWither($reflectionMethod, $doc)) {
                            $withers[] = [$reflectionMethod->name, [], \true];
                        } else {
                            $value->addMethodCall($reflectionMethod->name, []);
                        }
                        break;
                    }
                    if (\false === \stripos($doc, '@inheritdoc') || !\preg_match('#(?:^/\\*\\*|\\n\\s*+\\*)\\s*+(?:\\{@inheritdoc\\}|@inheritdoc)(?:\\s|\\*/$)#i', $doc)) {
                        break;
                    }
                }
                try {
                    $r = $r->getPrototype();
                } catch (\ReflectionException $e) {
                    break;
                    // method has no prototype
                }
            }
        }
        if ($withers) {
            // Prepend withers to prevent creating circular loops
            $setters = $value->getMethodCalls();
            $value->setMethodCalls($withers);
            foreach ($setters as $call) {
                $value->addMethodCall($call[0], $call[1], $call[2] ?? \false);
            }
        }
        return $value;
    }
    private function isWither(\ReflectionMethod $reflectionMethod, string $doc) : bool
    {
        $match = \preg_match('#(?:^/\\*\\*|\\n\\s*+\\*)\\s*+@return\\s++(static|\\$this)[\\s\\*]#i', $doc, $matches);
        if ($match && 'static' === $matches[1]) {
            return \true;
        }
        if ($match && '$this' === $matches[1]) {
            return \false;
        }
        $reflectionType = $reflectionMethod->hasReturnType() ? $reflectionMethod->getReturnType() : null;
        return $reflectionType instanceof \ReflectionNamedType && 'static' === $reflectionType->getName();
    }
}
