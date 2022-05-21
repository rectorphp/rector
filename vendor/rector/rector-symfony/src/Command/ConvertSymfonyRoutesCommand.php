<?php

declare (strict_types=1);
namespace Rector\Symfony\Command;

use RectorPrefix20220521\Nette\Utils\Json;
use RectorPrefix20220521\Psr\Container\ContainerInterface;
use Rector\Core\Configuration\RectorConfigProvider;
use Rector\Core\Exception\ShouldNotHappenException;
use RectorPrefix20220521\Symfony\Component\Console\Command\Command;
use RectorPrefix20220521\Symfony\Component\Console\Input\InputInterface;
use RectorPrefix20220521\Symfony\Component\Console\Output\OutputInterface;
use RectorPrefix20220521\Webmozart\Assert\Assert;
final class ConvertSymfonyRoutesCommand extends \RectorPrefix20220521\Symfony\Component\Console\Command\Command
{
    /**
     * @readonly
     * @var \Rector\Core\Configuration\RectorConfigProvider
     */
    private $rectorConfigProvider;
    public function __construct(\Rector\Core\Configuration\RectorConfigProvider $rectorConfigProvider)
    {
        $this->rectorConfigProvider = $rectorConfigProvider;
        parent::__construct();
    }
    protected function configure() : void
    {
        $this->setName('convert-symfony-routes');
        $this->setDescription('Convert routes from YAML to resoled controller annotation');
    }
    protected function execute(\RectorPrefix20220521\Symfony\Component\Console\Input\InputInterface $input, \RectorPrefix20220521\Symfony\Component\Console\Output\OutputInterface $output) : int
    {
        // @todo extract method
        $symfonyContainerPhp = $this->rectorConfigProvider->getSymfonyContainerPhp();
        \RectorPrefix20220521\Webmozart\Assert\Assert::fileExists($symfonyContainerPhp);
        $container = (require_once $symfonyContainerPhp);
        \RectorPrefix20220521\Webmozart\Assert\Assert::isInstanceOf('Psr\\Container\\ContainerInterface', $container);
        /** @var ContainerInterface $container */
        if (!$container->has('router')) {
            throw new \Rector\Core\Exception\ShouldNotHappenException(\sprintf('Symfony container has no service "%s", maybe it is private', 'router'));
        }
        $router = $container->get('router');
        \RectorPrefix20220521\Webmozart\Assert\Assert::isInstanceOf('Symfony\\Component\\Routing\\RouterInterface', $router);
        $routeCollection = $router->getRouteCollection();
        $routes = \array_map(static function ($route) : array {
            return ['path' => $route->getPath(), 'host' => $route->getHost(), 'schemes' => $route->getSchemes(), 'methods' => $route->getMethods(), 'defaults' => $route->getDefaults(), 'requirements' => $route->getRequirements(), 'condition' => $route->getCondition()];
        }, $routeCollection->all());
        $content = \RectorPrefix20220521\Nette\Utils\Json::encode($routes, \RectorPrefix20220521\Nette\Utils\Json::PRETTY) . \PHP_EOL;
        $output->write($content, \false, \RectorPrefix20220521\Symfony\Component\Console\Output\OutputInterface::OUTPUT_RAW);
        // @todo invoke the converter
        return \RectorPrefix20220521\Symfony\Component\Console\Command\Command::SUCCESS;
    }
}
