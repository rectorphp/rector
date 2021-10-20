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

use RectorPrefix20211020\Symfony\Component\Config\Resource\ClassExistenceResource;
use RectorPrefix20211020\Symfony\Component\DependencyInjection\Argument\ServiceLocatorArgument;
use RectorPrefix20211020\Symfony\Component\DependencyInjection\Argument\TaggedIteratorArgument;
use RectorPrefix20211020\Symfony\Component\DependencyInjection\Attribute\TaggedIterator;
use RectorPrefix20211020\Symfony\Component\DependencyInjection\Attribute\TaggedLocator;
use RectorPrefix20211020\Symfony\Component\DependencyInjection\Attribute\Target;
use RectorPrefix20211020\Symfony\Component\DependencyInjection\ContainerBuilder;
use RectorPrefix20211020\Symfony\Component\DependencyInjection\Definition;
use RectorPrefix20211020\Symfony\Component\DependencyInjection\Exception\AutowiringFailedException;
use RectorPrefix20211020\Symfony\Component\DependencyInjection\Exception\RuntimeException;
use RectorPrefix20211020\Symfony\Component\DependencyInjection\LazyProxy\ProxyHelper;
use RectorPrefix20211020\Symfony\Component\DependencyInjection\TypedReference;
/**
 * Inspects existing service definitions and wires the autowired ones using the type hints of their classes.
 *
 * @author Kévin Dunglas <dunglas@gmail.com>
 * @author Nicolas Grekas <p@tchwork.com>
 */
class AutowirePass extends \RectorPrefix20211020\Symfony\Component\DependencyInjection\Compiler\AbstractRecursivePass
{
    private $types;
    private $ambiguousServiceTypes;
    private $autowiringAliases;
    private $lastFailure;
    private $throwOnAutowiringException;
    private $decoratedClass;
    private $decoratedId;
    private $methodCalls;
    private $getPreviousValue;
    private $decoratedMethodIndex;
    private $decoratedMethodArgumentIndex;
    private $typesClone;
    public function __construct(bool $throwOnAutowireException = \true)
    {
        $this->throwOnAutowiringException = $throwOnAutowireException;
    }
    /**
     * {@inheritdoc}
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    public function process($container)
    {
        try {
            $this->typesClone = clone $this;
            parent::process($container);
        } finally {
            $this->decoratedClass = null;
            $this->decoratedId = null;
            $this->methodCalls = null;
            $this->getPreviousValue = null;
            $this->decoratedMethodIndex = null;
            $this->decoratedMethodArgumentIndex = null;
            $this->typesClone = null;
        }
    }
    /**
     * {@inheritdoc}
     * @param bool $isRoot
     */
    protected function processValue($value, $isRoot = \false)
    {
        try {
            return $this->doProcessValue($value, $isRoot);
        } catch (\RectorPrefix20211020\Symfony\Component\DependencyInjection\Exception\AutowiringFailedException $e) {
            if ($this->throwOnAutowiringException) {
                throw $e;
            }
            $this->container->getDefinition($this->currentId)->addError($e->getMessageCallback() ?? $e->getMessage());
            return parent::processValue($value, $isRoot);
        }
    }
    /**
     * @return mixed
     */
    private function doProcessValue($value, bool $isRoot = \false)
    {
        if ($value instanceof \RectorPrefix20211020\Symfony\Component\DependencyInjection\TypedReference) {
            if ($ref = $this->getAutowiredReference($value)) {
                return $ref;
            }
            if (\RectorPrefix20211020\Symfony\Component\DependencyInjection\ContainerBuilder::RUNTIME_EXCEPTION_ON_INVALID_REFERENCE === $value->getInvalidBehavior()) {
                $message = $this->createTypeNotFoundMessageCallback($value, 'it');
                // since the error message varies by referenced id and $this->currentId, so should the id of the dummy errored definition
                $this->container->register($id = \sprintf('.errored.%s.%s', $this->currentId, (string) $value), $value->getType())->addError($message);
                return new \RectorPrefix20211020\Symfony\Component\DependencyInjection\TypedReference($id, $value->getType(), $value->getInvalidBehavior(), $value->getName());
            }
        }
        $value = parent::processValue($value, $isRoot);
        if (!$value instanceof \RectorPrefix20211020\Symfony\Component\DependencyInjection\Definition || !$value->isAutowired() || $value->isAbstract() || !$value->getClass()) {
            return $value;
        }
        if (!($reflectionClass = $this->container->getReflectionClass($value->getClass(), \false))) {
            $this->container->log($this, \sprintf('Skipping service "%s": Class or interface "%s" cannot be loaded.', $this->currentId, $value->getClass()));
            return $value;
        }
        $this->methodCalls = $value->getMethodCalls();
        try {
            $constructor = $this->getConstructor($value, \false);
        } catch (\RectorPrefix20211020\Symfony\Component\DependencyInjection\Exception\RuntimeException $e) {
            throw new \RectorPrefix20211020\Symfony\Component\DependencyInjection\Exception\AutowiringFailedException($this->currentId, $e->getMessage(), 0, $e);
        }
        if ($constructor) {
            \array_unshift($this->methodCalls, [$constructor, $value->getArguments()]);
        }
        $checkAttributes = 80000 <= \PHP_VERSION_ID && !$value->hasTag('container.ignore_attributes');
        $this->methodCalls = $this->autowireCalls($reflectionClass, $isRoot, $checkAttributes);
        if ($constructor) {
            [, $arguments] = \array_shift($this->methodCalls);
            if ($arguments !== $value->getArguments()) {
                $value->setArguments($arguments);
            }
        }
        if ($this->methodCalls !== $value->getMethodCalls()) {
            $value->setMethodCalls($this->methodCalls);
        }
        return $value;
    }
    private function autowireCalls(\ReflectionClass $reflectionClass, bool $isRoot, bool $checkAttributes) : array
    {
        $this->decoratedId = null;
        $this->decoratedClass = null;
        $this->getPreviousValue = null;
        if ($isRoot && ($definition = $this->container->getDefinition($this->currentId)) && ($decoratedDefinition = $definition->getDecoratedService()) && null !== ($innerId = $decoratedDefinition[0]) && $this->container->has($innerId)) {
            // If the class references to itself and is decorated, provide the inner service id and class to not get a circular reference
            $this->decoratedClass = $this->container->findDefinition($innerId)->getClass();
            $this->decoratedId = $decoratedDefinition[1] ?? $this->currentId . '.inner';
        }
        foreach ($this->methodCalls as $i => $call) {
            $this->decoratedMethodIndex = $i;
            [$method, $arguments] = $call;
            if ($method instanceof \ReflectionFunctionAbstract) {
                $reflectionMethod = $method;
            } else {
                $definition = new \RectorPrefix20211020\Symfony\Component\DependencyInjection\Definition($reflectionClass->name);
                try {
                    $reflectionMethod = $this->getReflectionMethod($definition, $method);
                } catch (\RectorPrefix20211020\Symfony\Component\DependencyInjection\Exception\RuntimeException $e) {
                    if ($definition->getFactory()) {
                        continue;
                    }
                    throw $e;
                }
            }
            $arguments = $this->autowireMethod($reflectionMethod, $arguments, $checkAttributes);
            if ($arguments !== $call[1]) {
                $this->methodCalls[$i][1] = $arguments;
            }
        }
        return $this->methodCalls;
    }
    /**
     * Autowires the constructor or a method.
     *
     * @return array The autowired arguments
     *
     * @throws AutowiringFailedException
     */
    private function autowireMethod(\ReflectionFunctionAbstract $reflectionMethod, array $arguments, bool $checkAttributes) : array
    {
        $class = $reflectionMethod instanceof \ReflectionMethod ? $reflectionMethod->class : $this->currentId;
        $method = $reflectionMethod->name;
        $parameters = $reflectionMethod->getParameters();
        if ($reflectionMethod->isVariadic()) {
            \array_pop($parameters);
        }
        foreach ($parameters as $index => $parameter) {
            if (\array_key_exists($index, $arguments) && '' !== $arguments[$index]) {
                continue;
            }
            $type = \RectorPrefix20211020\Symfony\Component\DependencyInjection\LazyProxy\ProxyHelper::getTypeHint($reflectionMethod, $parameter, \true);
            if ($checkAttributes) {
                foreach ($parameter->getAttributes() as $attribute) {
                    if (\RectorPrefix20211020\Symfony\Component\DependencyInjection\Attribute\TaggedIterator::class === $attribute->getName()) {
                        $attribute = $attribute->newInstance();
                        $arguments[$index] = new \RectorPrefix20211020\Symfony\Component\DependencyInjection\Argument\TaggedIteratorArgument($attribute->tag, $attribute->indexAttribute);
                        break;
                    }
                    if (\RectorPrefix20211020\Symfony\Component\DependencyInjection\Attribute\TaggedLocator::class === $attribute->getName()) {
                        $attribute = $attribute->newInstance();
                        $arguments[$index] = new \RectorPrefix20211020\Symfony\Component\DependencyInjection\Argument\ServiceLocatorArgument(new \RectorPrefix20211020\Symfony\Component\DependencyInjection\Argument\TaggedIteratorArgument($attribute->tag, $attribute->indexAttribute, null, \true));
                        break;
                    }
                }
                if ('' !== ($arguments[$index] ?? '')) {
                    continue;
                }
            }
            if (!$type) {
                if (isset($arguments[$index])) {
                    continue;
                }
                // no default value? Then fail
                if (!$parameter->isDefaultValueAvailable()) {
                    // For core classes, isDefaultValueAvailable() can
                    // be false when isOptional() returns true. If the
                    // argument *is* optional, allow it to be missing
                    if ($parameter->isOptional()) {
                        continue;
                    }
                    $type = \RectorPrefix20211020\Symfony\Component\DependencyInjection\LazyProxy\ProxyHelper::getTypeHint($reflectionMethod, $parameter, \false);
                    $type = $type ? \sprintf('is type-hinted "%s"', \ltrim($type, '\\')) : 'has no type-hint';
                    throw new \RectorPrefix20211020\Symfony\Component\DependencyInjection\Exception\AutowiringFailedException($this->currentId, \sprintf('Cannot autowire service "%s": argument "$%s" of method "%s()" %s, you should configure its value explicitly.', $this->currentId, $parameter->name, $class !== $this->currentId ? $class . '::' . $method : $method, $type));
                }
                // specifically pass the default value
                $arguments[$index] = $parameter->getDefaultValue();
                continue;
            }
            $getValue = function () use($type, $parameter, $class, $method) {
                if (!($value = $this->getAutowiredReference($ref = new \RectorPrefix20211020\Symfony\Component\DependencyInjection\TypedReference($type, $type, \RectorPrefix20211020\Symfony\Component\DependencyInjection\ContainerBuilder::EXCEPTION_ON_INVALID_REFERENCE, \RectorPrefix20211020\Symfony\Component\DependencyInjection\Attribute\Target::parseName($parameter))))) {
                    $failureMessage = $this->createTypeNotFoundMessageCallback($ref, \sprintf('argument "$%s" of method "%s()"', $parameter->name, $class !== $this->currentId ? $class . '::' . $method : $method));
                    if ($parameter->isDefaultValueAvailable()) {
                        $value = $parameter->getDefaultValue();
                    } elseif (!$parameter->allowsNull()) {
                        throw new \RectorPrefix20211020\Symfony\Component\DependencyInjection\Exception\AutowiringFailedException($this->currentId, $failureMessage);
                    }
                }
                return $value;
            };
            if ($this->decoratedClass && ($isDecorated = \is_a($this->decoratedClass, $type, \true))) {
                if ($this->getPreviousValue) {
                    // The inner service is injected only if there is only 1 argument matching the type of the decorated class
                    // across all arguments of all autowired methods.
                    // If a second matching argument is found, the default behavior is restored.
                    $getPreviousValue = $this->getPreviousValue;
                    $this->methodCalls[$this->decoratedMethodIndex][1][$this->decoratedMethodArgumentIndex] = $getPreviousValue();
                    $this->decoratedClass = null;
                    // Prevent further checks
                } else {
                    $arguments[$index] = new \RectorPrefix20211020\Symfony\Component\DependencyInjection\TypedReference($this->decoratedId, $this->decoratedClass);
                    $this->getPreviousValue = $getValue;
                    $this->decoratedMethodArgumentIndex = $index;
                    continue;
                }
            }
            $arguments[$index] = $getValue();
        }
        if ($parameters && !isset($arguments[++$index])) {
            while (0 <= --$index) {
                $parameter = $parameters[$index];
                if (!$parameter->isDefaultValueAvailable() || $parameter->getDefaultValue() !== $arguments[$index]) {
                    break;
                }
                unset($arguments[$index]);
            }
        }
        // it's possible index 1 was set, then index 0, then 2, etc
        // make sure that we re-order so they're injected as expected
        \ksort($arguments);
        return $arguments;
    }
    /**
     * Returns a reference to the service matching the given type, if any.
     */
    private function getAutowiredReference(\RectorPrefix20211020\Symfony\Component\DependencyInjection\TypedReference $reference) : ?\RectorPrefix20211020\Symfony\Component\DependencyInjection\TypedReference
    {
        $this->lastFailure = null;
        $type = $reference->getType();
        if ($type !== (string) $reference) {
            return $reference;
        }
        if (null !== ($name = $reference->getName())) {
            if ($this->container->has($alias = $type . ' $' . $name) && !$this->container->findDefinition($alias)->isAbstract()) {
                return new \RectorPrefix20211020\Symfony\Component\DependencyInjection\TypedReference($alias, $type, $reference->getInvalidBehavior());
            }
            if ($this->container->has($name) && !$this->container->findDefinition($name)->isAbstract()) {
                foreach ($this->container->getAliases() as $id => $alias) {
                    if ($name === (string) $alias && \strncmp($id, $type . ' $', \strlen($type . ' $')) === 0) {
                        return new \RectorPrefix20211020\Symfony\Component\DependencyInjection\TypedReference($name, $type, $reference->getInvalidBehavior());
                    }
                }
            }
        }
        if ($this->container->has($type) && !$this->container->findDefinition($type)->isAbstract()) {
            return new \RectorPrefix20211020\Symfony\Component\DependencyInjection\TypedReference($type, $type, $reference->getInvalidBehavior());
        }
        return null;
    }
    /**
     * Populates the list of available types.
     */
    private function populateAvailableTypes(\RectorPrefix20211020\Symfony\Component\DependencyInjection\ContainerBuilder $container)
    {
        $this->types = [];
        $this->ambiguousServiceTypes = [];
        $this->autowiringAliases = [];
        foreach ($container->getDefinitions() as $id => $definition) {
            $this->populateAvailableType($container, $id, $definition);
        }
        foreach ($container->getAliases() as $id => $alias) {
            $this->populateAutowiringAlias($id);
        }
    }
    /**
     * Populates the list of available types for a given definition.
     */
    private function populateAvailableType(\RectorPrefix20211020\Symfony\Component\DependencyInjection\ContainerBuilder $container, string $id, \RectorPrefix20211020\Symfony\Component\DependencyInjection\Definition $definition)
    {
        // Never use abstract services
        if ($definition->isAbstract()) {
            return;
        }
        if ('' === $id || '.' === $id[0] || $definition->isDeprecated() || !($reflectionClass = $container->getReflectionClass($definition->getClass(), \false))) {
            return;
        }
        foreach ($reflectionClass->getInterfaces() as $reflectionInterface) {
            $this->set($reflectionInterface->name, $id);
        }
        do {
            $this->set($reflectionClass->name, $id);
        } while ($reflectionClass = $reflectionClass->getParentClass());
        $this->populateAutowiringAlias($id);
    }
    /**
     * Associates a type and a service id if applicable.
     */
    private function set(string $type, string $id)
    {
        // is this already a type/class that is known to match multiple services?
        if (isset($this->ambiguousServiceTypes[$type])) {
            $this->ambiguousServiceTypes[$type][] = $id;
            return;
        }
        // check to make sure the type doesn't match multiple services
        if (!isset($this->types[$type]) || $this->types[$type] === $id) {
            $this->types[$type] = $id;
            return;
        }
        // keep an array of all services matching this type
        if (!isset($this->ambiguousServiceTypes[$type])) {
            $this->ambiguousServiceTypes[$type] = [$this->types[$type]];
            unset($this->types[$type]);
        }
        $this->ambiguousServiceTypes[$type][] = $id;
    }
    private function createTypeNotFoundMessageCallback(\RectorPrefix20211020\Symfony\Component\DependencyInjection\TypedReference $reference, string $label) : \Closure
    {
        if (null === $this->typesClone->container) {
            $this->typesClone->container = new \RectorPrefix20211020\Symfony\Component\DependencyInjection\ContainerBuilder($this->container->getParameterBag());
            $this->typesClone->container->setAliases($this->container->getAliases());
            $this->typesClone->container->setDefinitions($this->container->getDefinitions());
            $this->typesClone->container->setResourceTracking(\false);
        }
        $currentId = $this->currentId;
        return (function () use($reference, $label, $currentId) {
            return $this->createTypeNotFoundMessage($reference, $label, $currentId);
        })->bindTo($this->typesClone);
    }
    private function createTypeNotFoundMessage(\RectorPrefix20211020\Symfony\Component\DependencyInjection\TypedReference $reference, string $label, string $currentId) : string
    {
        if (!($r = $this->container->getReflectionClass($type = $reference->getType(), \false))) {
            // either $type does not exist or a parent class does not exist
            try {
                $resource = new \RectorPrefix20211020\Symfony\Component\Config\Resource\ClassExistenceResource($type, \false);
                // isFresh() will explode ONLY if a parent class/trait does not exist
                $resource->isFresh(0);
                $parentMsg = \false;
            } catch (\ReflectionException $e) {
                $parentMsg = $e->getMessage();
            }
            $message = \sprintf('has type "%s" but this class %s.', $type, $parentMsg ? \sprintf('is missing a parent class (%s)', $parentMsg) : 'was not found');
        } else {
            $alternatives = $this->createTypeAlternatives($this->container, $reference);
            $message = $this->container->has($type) ? 'this service is abstract' : 'no such service exists';
            $message = \sprintf('references %s "%s" but %s.%s', $r->isInterface() ? 'interface' : 'class', $type, $message, $alternatives);
            if ($r->isInterface() && !$alternatives) {
                $message .= ' Did you create a class that implements this interface?';
            }
        }
        $message = \sprintf('Cannot autowire service "%s": %s %s', $currentId, $label, $message);
        if (null !== $this->lastFailure) {
            $message = $this->lastFailure . "\n" . $message;
            $this->lastFailure = null;
        }
        return $message;
    }
    private function createTypeAlternatives(\RectorPrefix20211020\Symfony\Component\DependencyInjection\ContainerBuilder $container, \RectorPrefix20211020\Symfony\Component\DependencyInjection\TypedReference $reference) : string
    {
        // try suggesting available aliases first
        if ($message = $this->getAliasesSuggestionForType($container, $type = $reference->getType())) {
            return ' ' . $message;
        }
        if (null === $this->ambiguousServiceTypes) {
            $this->populateAvailableTypes($container);
        }
        $servicesAndAliases = $container->getServiceIds();
        if (null !== ($autowiringAliases = $this->autowiringAliases[$type] ?? null) && !isset($autowiringAliases[''])) {
            return \sprintf(' Available autowiring aliases for this %s are: "$%s".', \class_exists($type, \false) ? 'class' : 'interface', \implode('", "$', $autowiringAliases));
        }
        if (!$container->has($type) && \false !== ($key = \array_search(\strtolower($type), \array_map('strtolower', $servicesAndAliases)))) {
            return \sprintf(' Did you mean "%s"?', $servicesAndAliases[$key]);
        } elseif (isset($this->ambiguousServiceTypes[$type])) {
            $message = \sprintf('one of these existing services: "%s"', \implode('", "', $this->ambiguousServiceTypes[$type]));
        } elseif (isset($this->types[$type])) {
            $message = \sprintf('the existing "%s" service', $this->types[$type]);
        } else {
            return '';
        }
        return \sprintf(' You should maybe alias this %s to %s.', \class_exists($type, \false) ? 'class' : 'interface', $message);
    }
    private function getAliasesSuggestionForType(\RectorPrefix20211020\Symfony\Component\DependencyInjection\ContainerBuilder $container, string $type) : ?string
    {
        $aliases = [];
        foreach (\class_parents($type) + \class_implements($type) as $parent) {
            if ($container->has($parent) && !$container->findDefinition($parent)->isAbstract()) {
                $aliases[] = $parent;
            }
        }
        if (1 < ($len = \count($aliases))) {
            $message = 'Try changing the type-hint to one of its parents: ';
            for ($i = 0, --$len; $i < $len; ++$i) {
                $message .= \sprintf('%s "%s", ', \class_exists($aliases[$i], \false) ? 'class' : 'interface', $aliases[$i]);
            }
            $message .= \sprintf('or %s "%s".', \class_exists($aliases[$i], \false) ? 'class' : 'interface', $aliases[$i]);
            return $message;
        }
        if ($aliases) {
            return \sprintf('Try changing the type-hint to "%s" instead.', $aliases[0]);
        }
        return null;
    }
    private function populateAutowiringAlias(string $id) : void
    {
        if (!\preg_match('/(?(DEFINE)(?<V>[a-zA-Z_\\x7f-\\xff][a-zA-Z0-9_\\x7f-\\xff]*+))^((?&V)(?:\\\\(?&V))*+)(?: \\$((?&V)))?$/', $id, $m)) {
            return;
        }
        $type = $m[2];
        $name = $m[3] ?? '';
        if (\class_exists($type, \false) || \interface_exists($type, \false)) {
            $this->autowiringAliases[$type][$name] = $name;
        }
    }
}
