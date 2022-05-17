<?php

declare (strict_types=1);
namespace Rector\Symfony\Command;

use RectorPrefix20220517\Symfony\Component\Console\Command\Command;
use RectorPrefix20220517\Symfony\Component\Console\Input\InputInterface;
use RectorPrefix20220517\Symfony\Component\Console\Output\OutputInterface;
use RectorPrefix20220517\Symfony\Component\Routing\Route;
use RectorPrefix20220517\Symfony\Component\Routing\RouterInterface;
/**
 * This command must be added to the application to export the routes to work with the "AddRouteAnnotationRector" rule
 */
final class RectorRoutesExportCommand extends \RectorPrefix20220517\Symfony\Component\Console\Command\Command
{
    protected static $defaultName = 'rector:routes:export';
    /**
     * @readonly
     * @var \Symfony\Component\Routing\RouterInterface
     */
    private $router;
    public function __construct(\RectorPrefix20220517\Symfony\Component\Routing\RouterInterface $router)
    {
        $this->router = $router;
        parent::__construct();
    }
    protected function configure() : void
    {
        $this->setDescription('Displays current routes for an application with resolved controller');
    }
    protected function execute(\RectorPrefix20220517\Symfony\Component\Console\Input\InputInterface $input, \RectorPrefix20220517\Symfony\Component\Console\Output\OutputInterface $output) : int
    {
        $routeCollection = $this->router->getRouteCollection();
        $routes = \array_map(static function (\RectorPrefix20220517\Symfony\Component\Routing\Route $route) : array {
            return ['path' => $route->getPath(), 'host' => $route->getHost(), 'schemes' => $route->getSchemes(), 'methods' => $route->getMethods(), 'defaults' => $route->getDefaults(), 'requirements' => $route->getRequirements(), 'condition' => $route->getCondition()];
        }, $routeCollection->all());
        $content = \json_encode($routes, \JSON_PRETTY_PRINT) . "\n";
        $output->write($content, \false, \RectorPrefix20220517\Symfony\Component\Console\Output\OutputInterface::OUTPUT_RAW);
        return \RectorPrefix20220517\Symfony\Component\Console\Command\Command::SUCCESS;
    }
}
