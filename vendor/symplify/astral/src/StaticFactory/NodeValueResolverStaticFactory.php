<?php

declare (strict_types=1);
namespace RectorPrefix202208\Symplify\Astral\StaticFactory;

use RectorPrefix202208\Symplify\Astral\NodeValue\NodeValueResolver;
use RectorPrefix202208\Symplify\PackageBuilder\Php\TypeChecker;
/**
 * @api
 */
final class NodeValueResolverStaticFactory
{
    public static function create() : NodeValueResolver
    {
        $simpleNameResolver = SimpleNameResolverStaticFactory::create();
        return new NodeValueResolver($simpleNameResolver, new TypeChecker());
    }
}
