<?php

declare (strict_types=1);
namespace Rector\Symfony\DataProvider;

use Rector\Core\Configuration\Option;
use Rector\Symfony\ValueObject\RouteMap\RouteMap;
use Rector\Symfony\ValueObjectFactory\RouteMapFactory;
use RectorPrefix20220516\Symplify\PackageBuilder\Parameter\ParameterProvider;
final class RouteMapProvider
{
    /**
     * @readonly
     * @var \Symplify\PackageBuilder\Parameter\ParameterProvider
     */
    private $parameterProvider;
    /**
     * @readonly
     * @var \Rector\Symfony\ValueObjectFactory\RouteMapFactory
     */
    private $routeMapFactory;
    public function __construct(\RectorPrefix20220516\Symplify\PackageBuilder\Parameter\ParameterProvider $parameterProvider, \Rector\Symfony\ValueObjectFactory\RouteMapFactory $routeMapFactory)
    {
        $this->parameterProvider = $parameterProvider;
        $this->routeMapFactory = $routeMapFactory;
    }
    public function provide() : \Rector\Symfony\ValueObject\RouteMap\RouteMap
    {
        $symfonyRoutesJsonPath = (string) $this->parameterProvider->provideParameter(\Rector\Core\Configuration\Option::SYMFONY_ROUTES_JSON_PATH_PARAMETER);
        if ($symfonyRoutesJsonPath === '') {
            return $this->routeMapFactory->createEmpty();
        }
        return $this->routeMapFactory->createFromFileContent($symfonyRoutesJsonPath);
    }
}
