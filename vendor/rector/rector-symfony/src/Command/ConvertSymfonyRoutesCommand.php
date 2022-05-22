<?php

declare (strict_types=1);
namespace Rector\Symfony\Command;

use RectorPrefix20220522\Nette\Utils\Json;
use Rector\Symfony\Bridge\Symfony\ContainerServiceProvider;
use RectorPrefix20220522\Symfony\Component\Console\Command\Command;
use RectorPrefix20220522\Symfony\Component\Console\Input\InputInterface;
use RectorPrefix20220522\Symfony\Component\Console\Output\OutputInterface;
use RectorPrefix20220522\Webmozart\Assert\Assert;
final class ConvertSymfonyRoutesCommand extends \RectorPrefix20220522\Symfony\Component\Console\Command\Command
{
    /**
     * @readonly
     * @var \Rector\Symfony\Bridge\Symfony\ContainerServiceProvider
     */
    private $containerServiceProvider;
    public function __construct(\Rector\Symfony\Bridge\Symfony\ContainerServiceProvider $containerServiceProvider)
    {
        $this->containerServiceProvider = $containerServiceProvider;
        parent::__construct();
    }
    protected function configure() : void
    {
        $this->setName('convert-symfony-routes');
        $this->setDescription('Convert routes from YAML to resoled controller annotation');
    }
    protected function execute(\RectorPrefix20220522\Symfony\Component\Console\Input\InputInterface $input, \RectorPrefix20220522\Symfony\Component\Console\Output\OutputInterface $output) : int
    {
        $router = $this->containerServiceProvider->provideByName('router');
        \RectorPrefix20220522\Webmozart\Assert\Assert::isInstanceOf($router, 'Symfony\\Component\\Routing\\RouterInterface');
        $routeCollection = $router->getRouteCollection();
        $routes = \array_map(static function ($route) : array {
            return ['path' => $route->getPath(), 'host' => $route->getHost(), 'schemes' => $route->getSchemes(), 'methods' => $route->getMethods(), 'defaults' => $route->getDefaults(), 'requirements' => $route->getRequirements(), 'condition' => $route->getCondition()];
        }, $routeCollection->all());
        $content = \RectorPrefix20220522\Nette\Utils\Json::encode($routes, \RectorPrefix20220522\Nette\Utils\Json::PRETTY) . \PHP_EOL;
        $output->write($content, \false, \RectorPrefix20220522\Symfony\Component\Console\Output\OutputInterface::OUTPUT_RAW);
        // @todo invoke the converter
        return \RectorPrefix20220522\Symfony\Component\Console\Command\Command::SUCCESS;
    }
}
