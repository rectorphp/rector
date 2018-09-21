<?php declare(strict_types=1);

namespace Rector\Symfony\Bridge;

use Rector\Bridge\Contract\AnalyzedApplicationContainerInterface;
use Rector\Configuration\Option;
use Rector\Exception\ShouldNotHappenException;
use Rector\Symfony\Bridge\DependencyInjection\ContainerFactory;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symplify\PackageBuilder\Parameter\ParameterProvider;
use Throwable;
use function Safe\class_implements;
use function Safe\sprintf;

final class DefaultAnalyzedSymfonyApplicationContainer implements AnalyzedApplicationContainerInterface
{
    /**
     * @var ParameterProvider
     */
    private $parameterProvider;

    /**
     * @var SymfonyKernelParameterGuard
     */
    private $symfonyKernelParameterGuard;

    /**
     * @var ContainerFactory
     */
    private $containerFactory;

    public function __construct(
        ParameterProvider $parameterProvider,
        SymfonyKernelParameterGuard $symfonyKernelParameterGuard,
        ContainerFactory $containerFactory
    ) {
        $this->parameterProvider = $parameterProvider;
        $this->symfonyKernelParameterGuard = $symfonyKernelParameterGuard;
        $this->containerFactory = $containerFactory;
    }

    public function getTypeForName(string $name): ?string
    {
        $container = $this->getContainer();

        if (! $container->has($name)) {
            return null;
        }

        try {
            $service = $container->get($name);
        } catch (Throwable $throwable) {
            throw new ShouldNotHappenException(sprintf(
                'Service type for "%s" name was not found in container of your Symfony application.',
                $name
            ), $throwable->getCode(), $throwable);
        }

        $serviceClass = get_class($service);
        if ($container->has($serviceClass)) {
            return $serviceClass;
        }

        // the type was not found in container → use it's interface or parent
        // mimics: \Symfony\Component\DependencyInjection\Compiler\AutowirePass::getAliasesSuggestionForType()
        foreach (class_implements($serviceClass) + class_parents($serviceClass) as $parent) {
            if ($container->has($parent) && ! $container->findDefinition($parent)->isAbstract()) {
                return $parent;
            }
        }

        throw new ShouldNotHappenException(sprintf(
            'Type "%s" was found for "%s" service name in container of your Symfony application, but it is not accessible.',
            get_class($service),
            $name
        ));
    }

    public function hasService(string $name): bool
    {
        $container = $this->getContainer();

        return $container->has($name);
    }

    /**
     * @return ContainerBuilder
     */
    private function getContainer(): Container
    {
        $kernelClass = $this->parameterProvider->provideParameter(Option::KERNEL_CLASS_PARAMETER);
        if ($kernelClass === null) {
            $kernelClass = $this->getDefaultKernelClass();
        }

        $this->symfonyKernelParameterGuard->ensureKernelClassIsValid($kernelClass);

        /** @var string $kernelClass */
        return $this->containerFactory->createFromKernelClass($kernelClass);
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
