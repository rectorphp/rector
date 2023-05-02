<?php

declare (strict_types=1);
namespace Rector\Symfony\Bridge\Symfony;

use Rector\Core\Configuration\RectorConfigProvider;
use Rector\Core\Exception\ShouldNotHappenException;
use RectorPrefix202305\Symfony\Component\DependencyInjection\Container;
use RectorPrefix202305\Webmozart\Assert\Assert;
final class ContainerServiceProvider
{
    /**
     * @var object|null
     */
    private $container;
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
        /** @var Container $symfonyContainer */
        $symfonyContainer = $this->getSymfonyContainer();
        if (!$symfonyContainer->has($serviceName)) {
            $errorMessage = \sprintf('Symfony container has no service "%s", maybe it is private', $serviceName);
            throw new ShouldNotHappenException($errorMessage);
        }
        return $symfonyContainer->get($serviceName);
    }
    private function getSymfonyContainer() : object
    {
        if ($this->container === null) {
            $symfonyContainerPhp = $this->rectorConfigProvider->getSymfonyContainerPhp();
            Assert::fileExists($symfonyContainerPhp);
            $container = (require $symfonyContainerPhp);
            // this allows older Symfony versions, e.g. 2.8 did not have the PSR yet
            Assert::isInstanceOf($container, 'Symfony\\Component\\DependencyInjection\\Container');
            $this->container = $container;
        }
        return $this->container;
    }
}
