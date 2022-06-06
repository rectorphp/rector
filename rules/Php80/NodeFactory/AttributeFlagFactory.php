<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Php80\NodeFactory;

use RectorPrefix20220606\PhpParser\Node\Expr;
use RectorPrefix20220606\PhpParser\Node\Expr\BinaryOp\BitwiseOr;
use RectorPrefix20220606\PhpParser\Node\Expr\ClassConstFetch;
final class AttributeFlagFactory
{
    /**
     * @param ClassConstFetch[] $classConstFetches
     * @return ClassConstFetch|BitwiseOr|null
     */
    public function createFlagCollection(array $classConstFetches) : ?Expr
    {
        if ($classConstFetches === []) {
            return null;
        }
        $flagCollection = \array_shift($classConstFetches);
        foreach ($classConstFetches as $classConstFetch) {
            $flagCollection = new BitwiseOr($flagCollection, $classConstFetch);
        }
        return $flagCollection;
    }
}
