<?php

declare (strict_types=1);
namespace RectorPrefix20220418\Symplify\Astral\StaticFactory;

use PhpParser\NodeFinder;
use RectorPrefix20220418\Symplify\Astral\NodeFinder\SimpleNodeFinder;
use RectorPrefix20220418\Symplify\Astral\NodeValue\NodeValueResolver;
use RectorPrefix20220418\Symplify\PackageBuilder\Php\TypeChecker;
/**
 * @api
 */
final class NodeValueResolverStaticFactory
{
    public static function create() : \RectorPrefix20220418\Symplify\Astral\NodeValue\NodeValueResolver
    {
        $simpleNameResolver = \RectorPrefix20220418\Symplify\Astral\StaticFactory\SimpleNameResolverStaticFactory::create();
        $simpleNodeFinder = new \RectorPrefix20220418\Symplify\Astral\NodeFinder\SimpleNodeFinder(new \PhpParser\NodeFinder());
        return new \RectorPrefix20220418\Symplify\Astral\NodeValue\NodeValueResolver($simpleNameResolver, new \RectorPrefix20220418\Symplify\PackageBuilder\Php\TypeChecker(), $simpleNodeFinder);
    }
}
