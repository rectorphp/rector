<?php

declare (strict_types=1);
namespace Rector\PhpDocParser\NodeValue\NodeValueResolver;

use PhpParser\Node\Expr;
use PhpParser\Node\Scalar\MagicConst;
use PhpParser\Node\Scalar\MagicConst\Dir;
use PhpParser\Node\Scalar\MagicConst\File;
use Rector\PhpDocParser\Contract\NodeValueResolver\NodeValueResolverInterface;
/**
 * @see \Rector\Tests\PhpDocParser\NodeValue\NodeValueResolverTest
 *
 * @implements NodeValueResolverInterface<MagicConst>
 */
final class MagicConstValueResolver implements NodeValueResolverInterface
{
    public function getType() : string
    {
        return MagicConst::class;
    }
    /**
     * @param MagicConst $expr
     */
    public function resolve(Expr $expr, string $currentFilePath) : ?string
    {
        if ($expr instanceof Dir) {
            return \dirname($currentFilePath);
        }
        if ($expr instanceof File) {
            return $currentFilePath;
        }
        return null;
    }
}
