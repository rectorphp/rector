<?php declare(strict_types=1);

namespace Rector\Symfony\Bridge;

use Rector\Bridge\Contract\AnalyzedApplicationContainerInterface;
use Rector\Symfony\Bridge\DependencyInjection\ContainerFactory;
use Rector\Configuration\Option;
use Symfony\Component\DependencyInjection\Container;
use Symplify\PackageBuilder\Parameter\ParameterProvider;

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

        if ($container->has($name)) {
            $definition = $container->get($name);

            return get_class($definition);
        }

        return null;
    }

    public function hasService(string $name): bool
    {
        $container = $this->getContainer();

        return $container->has($name);
    }

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
