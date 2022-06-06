<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Symplify\Astral\NodeValue\NodeValueResolver;

use RectorPrefix20220606\PhpParser\Node\Expr;
use RectorPrefix20220606\PhpParser\Node\Scalar\MagicConst;
use RectorPrefix20220606\PhpParser\Node\Scalar\MagicConst\Dir;
use RectorPrefix20220606\PhpParser\Node\Scalar\MagicConst\File;
use RectorPrefix20220606\Symplify\Astral\Contract\NodeValueResolver\NodeValueResolverInterface;
/**
 * @see \Symplify\Astral\Tests\NodeValue\NodeValueResolverTest
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
