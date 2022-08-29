<?php

declare (strict_types=1);
namespace Rector\PhpDocParser\NodeValue\NodeValueResolver;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ConstFetch;
use Rector\PhpDocParser\Contract\NodeValueResolver\NodeValueResolverInterface;
/**
 * @see \Rector\Tests\PhpDocParser\NodeValue\NodeValueResolverTest
 *
 * @implements NodeValueResolverInterface<ConstFetch>
 */
final class ConstFetchValueResolver implements NodeValueResolverInterface
{
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
        $constFetchName = $expr->name->toString();
        return \constant($constFetchName);
    }
}
