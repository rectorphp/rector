<?php

declare(strict_types=1);

namespace Rector\Symfony\Bridge;

use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use Rector\Bridge\Contract\AnalyzedApplicationContainerInterface;
use Rector\Configuration\Option;
use Rector\Exception\ShouldNotHappenException;
use Rector\Symfony\Bridge\DependencyInjection\SymfonyContainerFactory;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symplify\PackageBuilder\Parameter\ParameterProvider;
use Throwable;

final class DefaultAnalyzedSymfonyApplicationContainer implements AnalyzedApplicationContainerInterface
{
    /**
     * @var SymfonyKernelParameterGuard
     */
    private $symfonyKernelParameterGuard;

    /**
     * @var SymfonyContainerFactory
     */
    private $symfonyContainerFactory;

    /**
     * @var ParameterProvider
     */
    private $parameterProvider;

    /**
     * @var ContainerInterface[]
     */
    private $containerByKernelClass = [];

    /**
     * @var string[]
     */
    private $commonNamesToTypes = [
        'doctrine' => 'Symfony\Bridge\Doctrine\RegistryInterface',
        'doctrine.orm.entity_manager' => 'Doctrine\ORM\EntityManagerInterface',
        'doctrine.orm.default_entity_manager' => 'Doctrine\ORM\EntityManagerInterface',
    ];

    public function __construct(
        SymfonyKernelParameterGuard $symfonyKernelParameterGuard,
        SymfonyContainerFactory $symfonyContainerFactory,
        ParameterProvider $parameterProvider
    ) {
        $this->symfonyKernelParameterGuard = $symfonyKernelParameterGuard;
        $this->symfonyContainerFactory = $symfonyContainerFactory;
        $this->parameterProvider = $parameterProvider;
    }

    public function getTypeForName(string $name): Type
    {
        if (isset($this->commonNamesToTypes[$name])) {
            return new ObjectType($this->commonNamesToTypes[$name]);
        }

        // get known Symfony  types
        $container = $this->getContainer($name);

        if (! $container->has($name)) {
            return new MixedType();
        }

        try {
            $service = $container->get($name);
        } catch (Throwable $throwable) {
            throw new ShouldNotHappenException(sprintf(
                'Service type for "%s" name was not found in container of your Symfony application.',
                $name
            ), $throwable->getCode(), $throwable);
        }

        if ($service === null) {
            return new MixedType();
        }

        $serviceClass = get_class($service);
        if ($container->has($serviceClass)) {
            return new ObjectType($serviceClass);
        }

        // the type was not found in container → use it's interface or parent
        // mimics: \Symfony\Component\DependencyInjection\Compiler\AutowirePass::getAliasesSuggestionForType()
        foreach (class_implements($serviceClass) + class_parents($serviceClass) as $parent) {
            if ($container->has($parent) && ! $container->findDefinition($parent)->isAbstract()) {
                return new ObjectType($parent);
            }
        }

        // make an assumption
        return new ObjectType(get_class($service));
    }

    public function hasService(string $name): bool
    {
        $container = $this->getContainer($name);

        return $container->has($name);
    }

    /**
     * @return object
     */
    public function getService(string $name)
    {
        $container = $this->getContainer($name);

        return $container->get($name);
    }

    /**
     * @return ContainerBuilder
     */
    private function getContainer(string $requestServiceName): Container
    {
        $kernelClass = $this->resolveKernelClass();

        if (isset($this->containerByKernelClass[$kernelClass])) {
            return $this->containerByKernelClass[$kernelClass];
        }

        $this->symfonyKernelParameterGuard->ensureKernelClassIsValid($kernelClass, $requestServiceName);

        /** @var string $kernelClass */
        $container = $this->symfonyContainerFactory->createFromKernelClass($kernelClass);

        $this->containerByKernelClass[$kernelClass] = $container;

        return $container;
    }

    private function resolveKernelClass(): ?string
    {
        $kernelClassParameter = $this->parameterProvider->provideParameter(Option::KERNEL_CLASS_PARAMETER);
        if ($kernelClassParameter) {
            return $kernelClassParameter;
        }

        return $this->getDefaultKernelClass();
    }

    private function getDefaultKernelClass(): ?string
    {
        $possibleKernelClasses = ['App\Kernel', 'Kernel', 'AppKernel'];

        foreach ($possibleKernelClasses as $possibleKernelClass) {
            if (class_exists($possibleKernelClass)) {
                return $possibleKernelClass;
            }
        }

        return null;
    }
}
