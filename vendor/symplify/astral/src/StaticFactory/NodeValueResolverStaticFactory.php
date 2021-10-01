<?php

declare (strict_types=1);
namespace RectorPrefix20211001\Symplify\Astral\StaticFactory;

use PhpParser\NodeFinder;
use RectorPrefix20211001\Symplify\Astral\NodeFinder\SimpleNodeFinder;
use RectorPrefix20211001\Symplify\Astral\NodeValue\NodeValueResolver;
use RectorPrefix20211001\Symplify\PackageBuilder\Php\TypeChecker;
final class NodeValueResolverStaticFactory
{
    public static function create() : \RectorPrefix20211001\Symplify\Astral\NodeValue\NodeValueResolver
    {
        $simpleNameResolver = \RectorPrefix20211001\Symplify\Astral\StaticFactory\SimpleNameResolverStaticFactory::create();
        $simpleNodeFinder = new \RectorPrefix20211001\Symplify\Astral\NodeFinder\SimpleNodeFinder(new \RectorPrefix20211001\Symplify\PackageBuilder\Php\TypeChecker(), new \PhpParser\NodeFinder());
        return new \RectorPrefix20211001\Symplify\Astral\NodeValue\NodeValueResolver($simpleNameResolver, new \RectorPrefix20211001\Symplify\PackageBuilder\Php\TypeChecker(), $simpleNodeFinder);
    }
}
