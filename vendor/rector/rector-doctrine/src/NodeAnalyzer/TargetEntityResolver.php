<?php

declare (strict_types=1);
namespace Rector\Doctrine\NodeAnalyzer;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Scalar\String_;
use Rector\Core\Exception\NotImplementedYetException;
use Rector\NodeNameResolver\NodeNameResolver;
final class TargetEntityResolver
{
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(\Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }
    /**
     * @return string|null
     */
    public function resolveFromExpr(\PhpParser\Node\Expr $targetEntityExpr)
    {
        if ($targetEntityExpr instanceof \PhpParser\Node\Expr\ClassConstFetch) {
            return $this->nodeNameResolver->getName($targetEntityExpr->class);
        }
        if ($targetEntityExpr instanceof \PhpParser\Node\Scalar\String_) {
            return $targetEntityExpr->value;
        }
        $errorMessage = \sprintf('Add support for "%s" targetEntity in "%s"', \get_class($targetEntityExpr), self::class);
        throw new \Rector\Core\Exception\NotImplementedYetException($errorMessage);
    }
}
