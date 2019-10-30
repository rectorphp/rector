<?php

declare(strict_types=1);

namespace Rector\Symfony\Bridge\DependencyInjection;

use Nette\Utils\Random;
use Rector\Configuration\Option;
use Rector\Exception\ShouldNotHappenException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\FrozenParameterBag;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\HttpKernel\Kernel;
use Symplify\PackageBuilder\Parameter\ParameterProvider;
use Symplify\PackageBuilder\Reflection\PrivatesAccessor;
use Symplify\PackageBuilder\Reflection\PrivatesCaller;
use Throwable;

final class SymfonyContainerFactory
{
    /**
     * @var string
     */
    private const FALLBACK_ENVIRONMENT = 'dev';

    /**
     * @var Container[]
     */
    private $containersByKernelClass = [];

    /**
     * @var ParameterProvider
     */
    private $parameterProvider;

    public function __construct(ParameterProvider $parameterProvider)
    {
        $this->parameterProvider = $parameterProvider;
    }

    public function createFromKernelClass(string $kernelClass): Container
    {
        if (isset($this->containersByKernelClass[$kernelClass])) {
            return $this->containersByKernelClass[$kernelClass];
        }

        $environment = $this->resolveEnvironment();

        try {
            $debug = (bool) ($_ENV['APP_DEBUG'] ?? $_SERVER['APP_DEBUG'] ?? true);

            $container = $this->createContainerFromKernelClass($kernelClass, $environment, $debug);
        } catch (Throwable $throwable) {
            throw new ShouldNotHappenException(sprintf(
                'Kernel "%s" could not be instantiated for: %s.%sCurrent environment is "%s". Try changing it in "parameters > kernel_environment" in rector.yaml',
                $kernelClass,
                $throwable->getMessage(),
                PHP_EOL . PHP_EOL,
                $environment
            ), $throwable->getCode(), $throwable);
        }

        $this->containersByKernelClass[$kernelClass] = $container;

        return $container;
    }

    private function resolveEnvironment(): string
    {
        /** @var string|null $kernelEnvironment */
        $kernelEnvironment = $this->parameterProvider->provideParameter(Option::KERNEL_ENVIRONMENT_PARAMETER);
        if ($kernelEnvironment) {
            return $kernelEnvironment;
        }

        return $_ENV['APP_ENV'] ?? $_SERVER['APP_ENV'] ?? self::FALLBACK_ENVIRONMENT;
    }

    /**
     * Mimics https://github.com/symfony/symfony/blob/f834c9262b411aa5793fcea23694e3ad3b5acbb4/src/Symfony/Bundle/FrameworkBundle/Command/ContainerDebugCommand.php#L200-L203
     */
    private function createContainerFromKernelClass(string $kernelClass, string $environment, bool $debug): Container
    {
        /** @var Kernel $kernel */
        $kernel = new $kernelClass($environment, $debug);
        // preloads all the extensions and $containerBuilder->compile()
        $kernel->boot();

        /** @var ContainerBuilder $containerBuilder */
        $containerBuilder = (new PrivatesCaller())->callPrivateMethod($kernel, 'buildContainer');
        $containerBuilder->getCompilerPassConfig()->setRemovingPasses([]);

        // anonymous class on intention, since this depends on Symfony\DependencyInjection in rector-prefixed
        $containerBuilder->getCompilerPassConfig()->addPass(new class() implements CompilerPassInterface {
            public function process(ContainerBuilder $containerBuilder): void
            {
                foreach ($containerBuilder->getDefinitions() as $definition) {
                    $definition->setPublic(true);
                }
                foreach ($containerBuilder->getAliases() as $definition) {
                    $definition->setPublic(true);
                }
            }
        });

        $containerBuilder->compile();

        // solves "You have requested a synthetic service ("kernel"). The DIC does not know how to construct this service"
        $containerBuilder->set('kernel', $kernel);

        // solves "You have requested a non-existent parameter "container.build_id"
        if ($containerBuilder->getParameterBag() instanceof FrozenParameterBag) {
            $unfrozenParameterBag = new ParameterBag($containerBuilder->getParameterBag()->all());

            $privatesAccessor = new PrivatesAccessor();
            $privatesAccessor->setPrivateProperty($containerBuilder, 'parameterBag', $unfrozenParameterBag);
        }

        if (! $containerBuilder->hasParameter('container.build_id')) {
            $containerBuilder->setParameter('container.build_id', Random::generate(10));
        }

        return $containerBuilder;
    }
}
