<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Doctrine\NodeAnalyzer;

use RectorPrefix20220606\PhpParser\Node\Expr;
use RectorPrefix20220606\PhpParser\Node\Expr\ClassConstFetch;
use RectorPrefix20220606\PhpParser\Node\Scalar\String_;
use RectorPrefix20220606\PHPStan\Reflection\ReflectionProvider;
use RectorPrefix20220606\Rector\Core\Exception\NotImplementedYetException;
use RectorPrefix20220606\Rector\NodeNameResolver\NodeNameResolver;
final class TargetEntityResolver
{
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    public function __construct(NodeNameResolver $nodeNameResolver, ReflectionProvider $reflectionProvider)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->reflectionProvider = $reflectionProvider;
    }
    /**
     * @return string|null
     */
    public function resolveFromExpr(Expr $targetEntityExpr)
    {
        if ($targetEntityExpr instanceof ClassConstFetch) {
            $targetEntity = (string) $this->nodeNameResolver->getName($targetEntityExpr->class);
            if (!$this->reflectionProvider->hasClass($targetEntity)) {
                return null;
            }
            return $targetEntity;
        }
        if ($targetEntityExpr instanceof String_) {
            $targetEntity = $targetEntityExpr->value;
            if (!$this->reflectionProvider->hasClass($targetEntity)) {
                return null;
            }
            return $targetEntity;
        }
        $errorMessage = \sprintf('Add support for "%s" targetEntity in "%s"', \get_class($targetEntityExpr), self::class);
        throw new NotImplementedYetException($errorMessage);
    }
}
