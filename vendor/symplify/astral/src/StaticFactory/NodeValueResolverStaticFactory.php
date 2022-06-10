<?php

declare (strict_types=1);
namespace RectorPrefix20220610\Symplify\Astral\StaticFactory;

use RectorPrefix20220610\Symplify\Astral\NodeValue\NodeValueResolver;
use RectorPrefix20220610\Symplify\PackageBuilder\Php\TypeChecker;
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
