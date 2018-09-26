<?php declare(strict_types=1);

namespace Rector\DependencyInjection;

use Psr\Container\ContainerInterface;
use Rector\ParameterGuider\ParameterGuider;
use Symfony\Component\DependencyInjection\Container;
use function Safe\putenv;

final class ContainerFactory
{
    /**
     * @var ParameterGuider
     */
    private $parameterGuider;

    public function __construct()
    {
        $this->parameterGuider = new ParameterGuider();
    }

    public function create(): ContainerInterface
    {
        $appKernel = new RectorKernel();
        $appKernel->boot();
        // this is require to keep CLI verbosity independent on AppKernel dev/prod mode
        putenv('SHELL_VERBOSITY=0');

        /** @var Container $container */
        $container = $appKernel->getContainer();

        $this->parameterGuider->processParameters($container->getParameterBag());

        return $container;
    }

    /**
     * @param string[] $configFiles
     */
    public function createWithConfigFiles(array $configFiles): ContainerInterface
    {
        $appKernel = new RectorKernel($configFiles);
        $appKernel->boot();
        // this is require to keep CLI verbosity independent on AppKernel dev/prod mode
        putenv('SHELL_VERBOSITY=0');

        /** @var Container $container */
        $container = $appKernel->getContainer();

        $this->parameterGuider->processParameters($container->getParameterBag());

        return $container;
    }
}
