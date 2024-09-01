<?php

declare (strict_types=1);
namespace Rector\Symfony\Bridge\Symfony;

use Rector\Configuration\Option;
use Rector\Configuration\Parameter\SimpleParameterProvider;
use Rector\Exception\ShouldNotHappenException;
use RectorPrefix202409\Symfony\Component\DependencyInjection\Container;
use RectorPrefix202409\Webmozart\Assert\Assert;
final class ContainerServiceProvider
{
    /**
     * @var object|null
     */
    private $container;
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
            $symfonyContainerPhp = SimpleParameterProvider::provideStringParameter(Option::SYMFONY_CONTAINER_PHP_PATH_PARAMETER);
            Assert::fileExists($symfonyContainerPhp);
            $container = (require $symfonyContainerPhp);
            // this allows older Symfony versions, e.g. 2.8 did not have the PSR yet
            Assert::isInstanceOf($container, 'Symfony\\Component\\DependencyInjection\\Container');
            $this->container = $container;
        }
        return $this->container;
    }
}
