<?php

declare (strict_types=1);
namespace Rector\Symfony\Bridge\Symfony;

use Rector\Core\Configuration\RectorConfigProvider;
use Rector\Core\Exception\ShouldNotHappenException;
use RectorPrefix202301\Webmozart\Assert\Assert;
final class ContainerServiceProvider
{
    /**
     * @readonly
     * @var \Rector\Core\Configuration\RectorConfigProvider
     */
    private $rectorConfigProvider;
    public function __construct(RectorConfigProvider $rectorConfigProvider)
    {
        $this->rectorConfigProvider = $rectorConfigProvider;
    }
    public function provideByName(string $serviceName) : object
    {
        $symfonyContainerPhp = $this->rectorConfigProvider->getSymfonyContainerPhp();
        Assert::fileExists($symfonyContainerPhp);
        $container = (require_once $symfonyContainerPhp);
        // this allows older Symfony versions, e.g. 2.8 did not have the PSR yet
        Assert::isInstanceOf($container, 'Symfony\\Component\\DependencyInjection\\Container');
        if (!$container->has($serviceName)) {
            $errorMessage = \sprintf('Symfony container has no service "%s", maybe it is private', $serviceName);
            throw new ShouldNotHappenException($errorMessage);
        }
        return $container->get($serviceName);
    }
}
