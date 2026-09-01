<?php

declare (strict_types=1);
namespace RectorPrefix202609\Entropy\Container;

use RectorPrefix202609\Entropy\Attributes\RelatedTest;
use RectorPrefix202609\Entropy\Console\CommandRegistry;
use RectorPrefix202609\Entropy\Console\Contract\CommandInterface;
use RectorPrefix202609\Entropy\Container\Exception\CreateServiceException;
use RectorPrefix202609\Entropy\Container\Exception\RegisterServiceException;
use RectorPrefix202609\Entropy\Reflection\ParameterTypesResolver;
use RectorPrefix202609\Entropy\Tests\Container\Container\ContainerTest;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;
use RectorPrefix202609\Webmozart\Assert\Assert;
/**
 * Designed to be extended by applications that need to customise resolution
 * (e.g. add their own service kinds), so this class is intentionally not final.
 *
 * @api extendable container
 */
class Container
{
    /**
     * @var array<class-string, callable(Container): object>
     */
    private array $serviceFactories = [];
    /**
     * @var array<class-string, object>
     */
    private array $instances = [];
    /**
     * Detects circular dependencies
     * @var array<class-string, true>
     */
    private array $making = [];
    /**
     * Detects circular dependencies
     * @var list<class-string>
     */
    private array $makingStack = [];
    /**
     * Classes registered for autowiring and contract discovery, without a factory.
     * @var array<class-string, true>
     */
    private array $registeredClasses = [];
    /**
     * Callbacks run once, right after a matching instance is built, keyed by the type they apply to.
     * @var array<class-string, list<callable(object, self): void>>
     */
    private array $afterResolvingCallbacks = [];
    /**
     * Instances built during the current top-level make() that still wait for their afterResolving
     * callbacks. Draining is deferred to the outermost make() so a service and the collection it
     * belongs to can resolve without tripping the circular-dependency guard.
     * @var list<object>
     */
    private array $pendingAfterResolving = [];
    private int $resolutionDepth = 0;
    private bool $isDraining = \false;
    public function __construct()
    {
        // setup default console service
        $this->service(CommandRegistry::class, function (Container $container): CommandRegistry {
            $commands = $container->findByContract(CommandInterface::class);
            return new CommandRegistry($commands);
        });
    }
    /**
     * Register service from provided directory
     */
    public function autodiscover(string $directory): void
    {
        Assert::directory($directory);
        $autodiscovery = new Autodiscovery();
        $serviceClassNames = $autodiscovery->autodiscoverDirectory($directory);
        foreach ($serviceClassNames as $serviceClassName) {
            // already instantiated
            if (isset($this->instances[$serviceClassName])) {
                continue;
            }
            // already registered as service
            if (isset($this->serviceFactories[$serviceClassName])) {
                continue;
            }
            // lazy factory
            $this->serviceFactories[$serviceClassName] = function (Container $container) use ($serviceClassName): object {
                $reflectionClass = new ReflectionClass($serviceClassName);
                return $this->createInstanceFromReflection($reflectionClass);
            };
        }
    }
    /**
     * @template TType as object
     *
     * @param class-string<TType> $class
     * @param callable(Container $container): TType $factory
     */
    public function service(string $class, callable $factory): void
    {
        if (isset($this->serviceFactories[$class])) {
            // avoid service override
            throw new RegisterServiceException(sprintf('Service for "%s" class is already registered', $class));
        }
        // a factory supersedes a bare registration of the same class
        unset($this->registeredClasses[$class]);
        $this->serviceFactories[$class] = $factory;
    }
    /**
     * Register a class for autowiring and contract discovery, without a factory. The container builds it
     * via reflection on demand, and findByContract() can then find it among its implementations.
     * Idempotent, unlike service(); a class already backed by a factory is left untouched.
     *
     * @param class-string $class
     */
    public function register(string $class): void
    {
        if (isset($this->serviceFactories[$class])) {
            return;
        }
        $this->registeredClasses[$class] = \true;
    }
    /**
     * Register a callback run once, right after an instance of $class (or a subtype) is built.
     * Useful for setter injection that would otherwise create a dependency cycle. Draining is
     * deferred to the outermost make(), so the callback can resolve the collection $class belongs to.
     *
     * @template TObject of object
     * @param class-string<TObject> $class
     * @param callable(TObject, self): void $callback
     */
    public function afterResolving(string $class, callable $callback): void
    {
        // wrap in a widening closure so the heterogeneous store stays type-safe; the guard narrows
        // the built instance back to TObject before handing it to the typed callback
        $this->afterResolvingCallbacks[$class][] = function (object $instance, self $container) use ($callback, $class): void {
            if ($instance instanceof $class) {
                $callback($instance, $container);
            }
        };
    }
    /**
     * @template TType as object
     *
     * @param class-string<TType> $class
     * @return TType
     */
    public function make(string $class): object
    {
        // use cached
        if (isset($this->instances[$class])) {
            return $this->instances[$class];
        }
        // circular dependency detection
        if (isset($this->making[$class])) {
            // Build a helpful cycle message: A -> B -> C -> A
            $cycleStartIndex = array_search($class, $this->makingStack, \true);
            $cycle = $cycleStartIndex === \false ? array_merge($this->makingStack, [$class]) : array_merge(array_slice($this->makingStack, $cycleStartIndex), [$class]);
            throw new CreateServiceException(sprintf('Circular dependency detected: %s', implode(' -> ', $cycle)));
        }
        // mark as "currently being created"
        $this->making[$class] = \true;
        $this->makingStack[] = $class;
        ++$this->resolutionDepth;
        try {
            // factories / registered services
            if (isset($this->serviceFactories[$class])) {
                $factory = $this->serviceFactories[$class];
                $instance = $factory($this);
                $this->instances[$class] = $instance;
                $this->queueAfterResolving($instance);
                return $instance;
            }
            // autowire via reflection
            $reflectionClass = new ReflectionClass($class);
            if ($reflectionClass->isInstantiable()) {
                $instance = $this->createInstanceFromReflection($reflectionClass);
                $this->instances[$class] = $instance;
                $this->queueAfterResolving($instance);
                return $instance;
            }
            throw new CreateServiceException(sprintf('No service found for "%s" class', $class));
        } finally {
            // always unmark, even if construction throws
            array_pop($this->makingStack);
            unset($this->making[$class]);
            // drain queued callbacks once the outermost make() has unwound, so their re-entrant
            // lookups see fully built instances instead of hitting the circular-dependency guard
            --$this->resolutionDepth;
            if ($this->resolutionDepth === 0 && !$this->isDraining) {
                $this->drainAfterResolving();
            }
        }
    }
    /**
     * @template TType as object
     *
     * @param class-string<TType> $contractClass
     * @return list<TType>
     */
    public function findByContract(string $contractClass): array
    {
        $this->warmUpInstanceServices($contractClass);
        $matches = array_filter($this->instances, fn(object $instance): bool => $instance instanceof $contractClass);
        // return a plain 0-indexed list; class-string keys would turn a variadic spread
        // (e.g. new Traverser(...$services)) into named arguments
        return array_values($matches);
    }
    /**
     * Forget every factory, discovery registration and cached instance whose class is-a $contract,
     * so make() and findByContract() no longer return them. A later make() rebuilds a fresh instance.
     *
     * @param class-string $contract
     */
    public function forgetByContract(string $contract): void
    {
        foreach (array_keys($this->serviceFactories) as $class) {
            if (is_a($class, $contract, \true)) {
                unset($this->serviceFactories[$class]);
            }
        }
        foreach (array_keys($this->registeredClasses) as $class) {
            if (is_a($class, $contract, \true)) {
                unset($this->registeredClasses[$class]);
            }
        }
        foreach (array_keys($this->instances) as $class) {
            if (is_a($class, $contract, \true)) {
                unset($this->instances[$class]);
            }
        }
    }
    /**
     * @param ReflectionParameter[] $reflectionParameters
     * @param class-string $class
     * @return array<object|object[]>
     */
    private function resolveDependenciesFromParameterReflections(ReflectionMethod $reflectionMethod, array $reflectionParameters, string $class): array
    {
        $parameterTypes = ParameterTypesResolver::resolve($reflectionMethod, $reflectionParameters, $class);
        $dependencies = [];
        foreach ($parameterTypes as $parameterType) {
            $dependencies[] = is_array($parameterType) ? $this->findByContract($parameterType[0]) : $this->make($parameterType);
        }
        return $dependencies;
    }
    private function warmUpInstanceServices(string $contractClass): void
    {
        // warm up both factory-backed services and bare-registered classes of the contract
        $knownClasses = array_merge(array_keys($this->serviceFactories), array_keys($this->registeredClasses));
        foreach ($knownClasses as $knownClass) {
            if (!is_a($knownClass, $contractClass, \true)) {
                continue;
            }
            if (isset($this->instances[$knownClass])) {
                continue;
            }
            // warm up cache if not yet
            $this->instances[$knownClass] = $this->make($knownClass);
        }
    }
    private function queueAfterResolving(object $instance): void
    {
        foreach (array_keys($this->afterResolvingCallbacks) as $registeredClass) {
            if ($instance instanceof $registeredClass) {
                $this->pendingAfterResolving[] = $instance;
                return;
            }
        }
    }
    private function drainAfterResolving(): void
    {
        $this->isDraining = \true;
        try {
            while ($this->pendingAfterResolving !== []) {
                $instance = array_shift($this->pendingAfterResolving);
                foreach ($this->afterResolvingCallbacks as $registeredClass => $callbacks) {
                    if (!$instance instanceof $registeredClass) {
                        continue;
                    }
                    foreach ($callbacks as $callback) {
                        $callback($instance, $this);
                    }
                }
            }
        } finally {
            $this->isDraining = \false;
        }
    }
    private function createInstanceFromReflection(ReflectionClass $reflectionClass): object
    {
        // try to create instance without reflectionParameters
        $constructorReflection = $reflectionClass->getConstructor();
        if ($constructorReflection === null || $constructorReflection->getNumberOfParameters() === 0) {
            $className = $reflectionClass->getName();
            return new $className();
        }
        // try to resolve dependencies
        $parameters = $constructorReflection->getParameters();
        $dependencies = $this->resolveDependenciesFromParameterReflections($constructorReflection, $parameters, $reflectionClass->getName());
        // create instance with resolved dependencies
        return $reflectionClass->newInstanceArgs($dependencies);
    }
}
