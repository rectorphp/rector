<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Symplify\Astral\StaticFactory;

use RectorPrefix20220606\PhpParser\NodeFinder;
use RectorPrefix20220606\Symplify\Astral\NodeFinder\SimpleNodeFinder;
use RectorPrefix20220606\Symplify\Astral\NodeValue\NodeValueResolver;
use RectorPrefix20220606\Symplify\PackageBuilder\Php\TypeChecker;
/**
 * @api
 */
final class NodeValueResolverStaticFactory
{
    public static function create() : NodeValueResolver
    {
        $simpleNameResolver = SimpleNameResolverStaticFactory::create();
        $simpleNodeFinder = new SimpleNodeFinder(new NodeFinder());
        return new NodeValueResolver($simpleNameResolver, new TypeChecker(), $simpleNodeFinder);
    }
}
