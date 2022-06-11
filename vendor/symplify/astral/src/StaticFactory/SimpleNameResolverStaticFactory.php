<?php

declare (strict_types=1);
namespace RectorPrefix202206\Symplify\Astral\StaticFactory;

use RectorPrefix202206\Symplify\Astral\Naming\SimpleNameResolver;
use RectorPrefix202206\Symplify\Astral\NodeNameResolver\ArgNodeNameResolver;
use RectorPrefix202206\Symplify\Astral\NodeNameResolver\AttributeNodeNameResolver;
use RectorPrefix202206\Symplify\Astral\NodeNameResolver\ClassLikeNodeNameResolver;
use RectorPrefix202206\Symplify\Astral\NodeNameResolver\ClassMethodNodeNameResolver;
use RectorPrefix202206\Symplify\Astral\NodeNameResolver\ConstFetchNodeNameResolver;
use RectorPrefix202206\Symplify\Astral\NodeNameResolver\FuncCallNodeNameResolver;
use RectorPrefix202206\Symplify\Astral\NodeNameResolver\IdentifierNodeNameResolver;
use RectorPrefix202206\Symplify\Astral\NodeNameResolver\NamespaceNodeNameResolver;
use RectorPrefix202206\Symplify\Astral\NodeNameResolver\ParamNodeNameResolver;
use RectorPrefix202206\Symplify\Astral\NodeNameResolver\PropertyNodeNameResolver;
/**
 * This would be normally handled by standard Symfony or Nette DI, but PHPStan does not use any of those, so we have to
 * make it manually.
 */
final class SimpleNameResolverStaticFactory
{
    public static function create() : SimpleNameResolver
    {
        $nameResolvers = [new ArgNodeNameResolver(), new AttributeNodeNameResolver(), new ClassLikeNodeNameResolver(), new ClassMethodNodeNameResolver(), new ConstFetchNodeNameResolver(), new FuncCallNodeNameResolver(), new IdentifierNodeNameResolver(), new NamespaceNodeNameResolver(), new ParamNodeNameResolver(), new PropertyNodeNameResolver()];
        return new SimpleNameResolver($nameResolvers);
    }
}
