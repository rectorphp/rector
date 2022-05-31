<?php

declare (strict_types=1);
namespace Rector\Symfony\Bridge\Symfony;

use Rector\Core\Configuration\RectorConfigProvider;
use Rector\Core\Exception\ShouldNotHappenException;
use RectorPrefix20220531\Webmozart\Assert\Assert;
final class ContainerServiceProvider
{
    /**
     * @readonly
     * @var \Rector\Core\Configuration\RectorConfigProvider
     */
    private $rectorConfigProvider;
    public function __construct(\Rector\Core\Configuration\RectorConfigProvider $rectorConfigProvider)
    {
        $this->rectorConfigProvider = $rectorConfigProvider;
    }
    public function provideByName(string $serviceName) : object
    {
        $symfonyContainerPhp = $this->rectorConfigProvider->getSymfonyContainerPhp();
        \RectorPrefix20220531\Webmozart\Assert\Assert::fileExists($symfonyContainerPhp);
        $container = (require_once $symfonyContainerPhp);
        // this allows older Symfony versions, e.g. 2.8 did not have the PSR yet
        \RectorPrefix20220531\Webmozart\Assert\Assert::isInstanceOf($container, 'Symfony\\Component\\DependencyInjection\\Container');
        if (!$container->has($serviceName)) {
            $errorMessage = \sprintf('Symfony container has no service "%s", maybe it is private', 'router');
            throw new \Rector\Core\Exception\ShouldNotHappenException($errorMessage);
        }
        return $container->get($serviceName);
    }
}
