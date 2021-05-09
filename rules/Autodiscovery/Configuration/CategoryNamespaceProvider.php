<?php

declare (strict_types=1);
namespace Rector\Autodiscovery\Configuration;

/**
 * List of namespace category that are usually 1-level nested (e.g. "Controller", "Entity", "Repository"...).
 * This is usually needed to find the "root" namespace,
 * e.g. App\Product\Controller → App\Product
 */
final class CategoryNamespaceProvider
{
    /**
     * @var string[]
     */
    private const CATEGORY_NAMESPACES = ['Command', 'Controller', 'Exception', 'Facade', 'EventSubscriber', 'EventListener', 'Entity', 'EntityFactory', 'Repository', 'Validator', 'Validation'];
    /**
     * @return string[]
     */
    public function provide() : array
    {
        return self::CATEGORY_NAMESPACES;
    }
}
