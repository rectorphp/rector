<?php

declare (strict_types=1);
namespace Rector\Php80\NodeFactory;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\BitwiseOr;
use PhpParser\Node\Expr\ClassConstFetch;
final class AttributeFlagFactory
{
    /**
     * @param ClassConstFetch[] $classConstFetches
     * @return ClassConstFetch|BitwiseOr|null
     */
    public function createFlagCollection(array $classConstFetches) : ?\PhpParser\Node\Expr
    {
        if ($classConstFetches === []) {
            return null;
        }
        $flagCollection = \array_shift($classConstFetches);
        foreach ($classConstFetches as $classConstFetch) {
            $flagCollection = new \PhpParser\Node\Expr\BinaryOp\BitwiseOr($flagCollection, $classConstFetch);
        }
        return $flagCollection;
    }
}
