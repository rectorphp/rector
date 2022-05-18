<?php

declare (strict_types=1);
namespace Rector\Symfony\ValueObjectFactory;

use Rector\Symfony\Exception\JsonRoutesNotExistsException;
use Rector\Symfony\ValueObject\RouteMap\RouteMap;
use Rector\Symfony\ValueObject\SymfonyRouteMetadata;
use RectorPrefix20220518\Symplify\SmartFileSystem\SmartFileSystem;
final class RouteMapFactory
{
    /**
     * @readonly
     * @var \Symplify\SmartFileSystem\SmartFileSystem
     */
    private $smartFileSystem;
    public function __construct(\RectorPrefix20220518\Symplify\SmartFileSystem\SmartFileSystem $smartFileSystem)
    {
        $this->smartFileSystem = $smartFileSystem;
    }
    public function createFromFileContent(string $configFilePath) : \Rector\Symfony\ValueObject\RouteMap\RouteMap
    {
        $fileContents = $this->smartFileSystem->readFile($configFilePath);
        $json = \json_decode($fileContents, \true, 512, 0);
        if ($json === \false) {
            throw new \Rector\Symfony\Exception\JsonRoutesNotExistsException(\sprintf('Routes "%s" cannot be parsed', $configFilePath));
        }
        /** @var SymfonyRouteMetadata[] $routes */
        $routes = [];
        foreach ($json as $name => $def) {
            $routes[$name] = new \Rector\Symfony\ValueObject\SymfonyRouteMetadata($name, $def['path'], $def['defaults'], $def['requirements'], $def['host'], $def['schemes'], $def['methods'], $def['condition']);
        }
        return new \Rector\Symfony\ValueObject\RouteMap\RouteMap($routes);
    }
    public function createEmpty() : \Rector\Symfony\ValueObject\RouteMap\RouteMap
    {
        return new \Rector\Symfony\ValueObject\RouteMap\RouteMap([]);
    }
}
