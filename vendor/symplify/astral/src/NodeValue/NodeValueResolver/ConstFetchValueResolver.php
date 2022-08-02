<?php

declare (strict_types=1);
namespace RectorPrefix202208\Symplify\Astral\NodeValue\NodeValueResolver;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ConstFetch;
use RectorPrefix202208\Symplify\Astral\Contract\NodeValueResolver\NodeValueResolverInterface;
use RectorPrefix202208\Symplify\Astral\Naming\SimpleNameResolver;
/**
 * @see \Symplify\Astral\Tests\NodeValue\NodeValueResolverTest
 *
 * @implements NodeValueResolverInterface<ConstFetch>
 */
final class ConstFetchValueResolver implements NodeValueResolverInterface
{
    /**
     * @var \Symplify\Astral\Naming\SimpleNameResolver
     */
    private $simpleNameResolver;
    public function __construct(SimpleNameResolver $simpleNameResolver)
    {
        $this->simpleNameResolver = $simpleNameResolver;
    }
    public function getType() : string
    {
        return ConstFetch::class;
    }
    /**
     * @param ConstFetch $expr
     * @return mixed
     */
    public function resolve(Expr $expr, string $currentFilePath)
    {
        $constFetchName = $this->simpleNameResolver->getName($expr);
        if ($constFetchName === null) {
            return null;
        }
        return \constant($constFetchName);
    }
}
