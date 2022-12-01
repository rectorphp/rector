<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix202212\Symfony\Component\VarExporter\Internal;

use RectorPrefix202212\Symfony\Component\VarExporter\Hydrator as PublicHydrator;
/**
 * Keeps the state of lazy objects.
 *
 * As a micro-optimization, this class uses no type declarations.
 *
 * @internal
 */
class LazyObjectState
{
    public const STATUS_UNINITIALIZED_FULL = 1;
    public const STATUS_UNINITIALIZED_PARTIAL = 2;
    public const STATUS_INITIALIZED_FULL = 3;
    public const STATUS_INITIALIZED_PARTIAL = 4;
    /**
     * @var array<string, true>
     * @readonly
     */
    public $skippedProperties;
    /**
     * @var self::STATUS_*
     */
    public $status = 0;
    /**
     * @readonly
     * @var \Closure|mixed[]
     */
    public $initializer;
    /**
     * @param \Closure|mixed[] $initializer
     */
    public function __construct($initializer, $skippedProperties = [])
    {
        $this->initializer = $initializer;
        $this->skippedProperties = $skippedProperties;
        $this->status = \is_array($initializer) ? self::STATUS_UNINITIALIZED_PARTIAL : self::STATUS_UNINITIALIZED_FULL;
    }
    public function initialize($instance, $propertyName, $propertyScope)
    {
        if (self::STATUS_INITIALIZED_FULL === $this->status) {
            return self::STATUS_INITIALIZED_FULL;
        }
        if (\is_array($this->initializer)) {
            $class = \get_class($instance);
            $propertyScope = $propertyScope ?? $class;
            $propertyScopes = Hydrator::$propertyScopes[$class];
            $propertyScopes[$k = "\x00{$propertyScope}\x00{$propertyName}"] ?? $propertyScopes[$k = "\x00*\x00{$propertyName}"] ?? ($k = $propertyName);
            if ($initializer = $this->initializer[$k] ?? null) {
                $value = $initializer(...[$instance, $propertyName, $propertyScope, LazyObjectRegistry::$defaultProperties[$class][$k] ?? null]);
                $accessor = LazyObjectRegistry::$classAccessors[$propertyScope] = LazyObjectRegistry::$classAccessors[$propertyScope] ?? LazyObjectRegistry::getClassAccessors($propertyScope);
                $accessor['set']($instance, $propertyName, $value);
                return $this->status = self::STATUS_INITIALIZED_PARTIAL;
            }
            $status = self::STATUS_UNINITIALIZED_PARTIAL;
            if ($initializer = $this->initializer["\x00"] ?? null) {
                if (!\is_array($values = $initializer($instance, LazyObjectRegistry::$defaultProperties[$class]))) {
                    throw new \TypeError(\sprintf('The lazy-initializer defined for instance of "%s" must return an array, got "%s".', $class, \get_debug_type($values)));
                }
                $properties = (array) $instance;
                foreach ($values as $key => $value) {
                    if ($k === $key) {
                        $status = self::STATUS_INITIALIZED_PARTIAL;
                    }
                    if (!\array_key_exists($key, $properties) && ([$scope, $name, $readonlyScope] = $propertyScopes[$key] ?? null)) {
                        $scope = $readonlyScope ?? ('*' !== $scope ? $scope : $class);
                        $accessor = LazyObjectRegistry::$classAccessors[$scope] = LazyObjectRegistry::$classAccessors[$scope] ?? LazyObjectRegistry::getClassAccessors($scope);
                        $accessor['set']($instance, $name, $value);
                    }
                }
            }
            return $status;
        }
        $this->status = self::STATUS_INITIALIZED_FULL;
        try {
            if ($defaultProperties = \array_diff_key(LazyObjectRegistry::$defaultProperties[\get_class($instance)], $this->skippedProperties)) {
                PublicHydrator::hydrate($instance, $defaultProperties);
            }
            ($this->initializer)($instance);
        } catch (\Throwable $e) {
            $this->status = self::STATUS_UNINITIALIZED_FULL;
            $this->reset($instance);
            throw $e;
        }
        return self::STATUS_INITIALIZED_FULL;
    }
    public function reset($instance) : void
    {
        $class = \get_class($instance);
        $propertyScopes = Hydrator::$propertyScopes[$class] = Hydrator::$propertyScopes[$class] ?? Hydrator::getPropertyScopes($class);
        $skippedProperties = $this->skippedProperties;
        $properties = (array) $instance;
        $onlyProperties = \is_array($this->initializer) ? $this->initializer : null;
        foreach ($propertyScopes as $key => [$scope, $name, $readonlyScope]) {
            $propertyScopes[$k = "\x00{$scope}\x00{$name}"] ?? $propertyScopes[$k = "\x00*\x00{$name}"] ?? ($k = $name);
            if ($k === $key && (null !== $readonlyScope || !\array_key_exists($k, $properties))) {
                $skippedProperties[$k] = \true;
            }
        }
        foreach (LazyObjectRegistry::$classResetters[$class] as $reset) {
            $reset($instance, $skippedProperties, $onlyProperties);
        }
        $this->status = self::STATUS_INITIALIZED_FULL === $this->status ? self::STATUS_UNINITIALIZED_FULL : self::STATUS_UNINITIALIZED_PARTIAL;
    }
}
