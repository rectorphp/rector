<?php

declare (strict_types=1);
namespace RectorPrefix20220111\Symplify\Astral\StaticFactory;

use PhpParser\NodeFinder;
use RectorPrefix20220111\Symplify\Astral\NodeFinder\SimpleNodeFinder;
use RectorPrefix20220111\Symplify\Astral\NodeValue\NodeValueResolver;
use RectorPrefix20220111\Symplify\PackageBuilder\Php\TypeChecker;
/**
 * @api
 */
final class NodeValueResolverStaticFactory
{
    public static function create() : \RectorPrefix20220111\Symplify\Astral\NodeValue\NodeValueResolver
    {
        $simpleNameResolver = \RectorPrefix20220111\Symplify\Astral\StaticFactory\SimpleNameResolverStaticFactory::create();
        $simpleNodeFinder = new \RectorPrefix20220111\Symplify\Astral\NodeFinder\SimpleNodeFinder(new \RectorPrefix20220111\Symplify\PackageBuilder\Php\TypeChecker(), new \PhpParser\NodeFinder());
        return new \RectorPrefix20220111\Symplify\Astral\NodeValue\NodeValueResolver($simpleNameResolver, new \RectorPrefix20220111\Symplify\PackageBuilder\Php\TypeChecker(), $simpleNodeFinder);
    }
}
