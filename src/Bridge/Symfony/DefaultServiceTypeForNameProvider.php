<?php declare(strict_types=1);

namespace Rector\Bridge\Symfony;

use Rector\Bridge\Symfony\DependencyInjection\ContainerFactory;
use Rector\Configuration\Option;
use Rector\Contract\Bridge\ServiceTypeForNameProviderInterface;
use Symplify\PackageBuilder\Parameter\ParameterProvider;

final class DefaultServiceTypeForNameProvider implements ServiceTypeForNameProviderInterface
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

    public function provideTypeForName(string $name): ?string
    {
        $kernelClass = $this->parameterProvider->provideParameter(Option::KERNEL_CLASS_PARAMETER);
        $this->symfonyKernelParameterGuard->ensureKernelClassIsValid($kernelClass);

        // make this default, register and require kernel_class paramter, see:
        // https://github.com/rectorphp/rector/issues/428

        /** @var string $kernelClass */
        $container = $this->containerFactory->createFromKernelClass($kernelClass);

        if ($container->has($name)) {
            $definition = $container->get($name);

            return get_class($definition);
        }

        return null;
    }
}
