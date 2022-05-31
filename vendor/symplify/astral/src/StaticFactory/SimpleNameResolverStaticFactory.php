<?php

declare (strict_types=1);
namespace RectorPrefix20220531\Symplify\Astral\StaticFactory;

use RectorPrefix20220531\Symplify\Astral\Naming\SimpleNameResolver;
use RectorPrefix20220531\Symplify\Astral\NodeNameResolver\ArgNodeNameResolver;
use RectorPrefix20220531\Symplify\Astral\NodeNameResolver\AttributeNodeNameResolver;
use RectorPrefix20220531\Symplify\Astral\NodeNameResolver\ClassLikeNodeNameResolver;
use RectorPrefix20220531\Symplify\Astral\NodeNameResolver\ClassMethodNodeNameResolver;
use RectorPrefix20220531\Symplify\Astral\NodeNameResolver\ConstFetchNodeNameResolver;
use RectorPrefix20220531\Symplify\Astral\NodeNameResolver\FuncCallNodeNameResolver;
use RectorPrefix20220531\Symplify\Astral\NodeNameResolver\IdentifierNodeNameResolver;
use RectorPrefix20220531\Symplify\Astral\NodeNameResolver\NamespaceNodeNameResolver;
use RectorPrefix20220531\Symplify\Astral\NodeNameResolver\ParamNodeNameResolver;
use RectorPrefix20220531\Symplify\Astral\NodeNameResolver\PropertyNodeNameResolver;
/**
 * This would be normally handled by standard Symfony or Nette DI, but PHPStan does not use any of those, so we have to
 * make it manually.
 */
final class SimpleNameResolverStaticFactory
{
    public static function create() : \RectorPrefix20220531\Symplify\Astral\Naming\SimpleNameResolver
    {
        $nameResolvers = [new \RectorPrefix20220531\Symplify\Astral\NodeNameResolver\ArgNodeNameResolver(), new \RectorPrefix20220531\Symplify\Astral\NodeNameResolver\AttributeNodeNameResolver(), new \RectorPrefix20220531\Symplify\Astral\NodeNameResolver\ClassLikeNodeNameResolver(), new \RectorPrefix20220531\Symplify\Astral\NodeNameResolver\ClassMethodNodeNameResolver(), new \RectorPrefix20220531\Symplify\Astral\NodeNameResolver\ConstFetchNodeNameResolver(), new \RectorPrefix20220531\Symplify\Astral\NodeNameResolver\FuncCallNodeNameResolver(), new \RectorPrefix20220531\Symplify\Astral\NodeNameResolver\IdentifierNodeNameResolver(), new \RectorPrefix20220531\Symplify\Astral\NodeNameResolver\NamespaceNodeNameResolver(), new \RectorPrefix20220531\Symplify\Astral\NodeNameResolver\ParamNodeNameResolver(), new \RectorPrefix20220531\Symplify\Astral\NodeNameResolver\PropertyNodeNameResolver()];
        return new \RectorPrefix20220531\Symplify\Astral\Naming\SimpleNameResolver($nameResolvers);
    }
}
