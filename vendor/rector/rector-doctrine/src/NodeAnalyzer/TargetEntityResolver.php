<?php

declare (strict_types=1);
namespace Rector\Doctrine\NodeAnalyzer;

use PhpParser\Node\Attribute;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\String_;
use PHPStan\Reflection\ReflectionProvider;
use Rector\Doctrine\CodeQuality\Enum\EntityMappingKey;
use Rector\Exception\NotImplementedYetException;
use Rector\NodeNameResolver\NodeNameResolver;
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
    public function resolveFromAttribute(Attribute $attribute) : ?string
    {
        foreach ($attribute->args as $arg) {
            if (!$arg->name instanceof Identifier) {
                continue;
            }
            if ($arg->name->toString() !== EntityMappingKey::TARGET_ENTITY) {
                continue;
            }
            return $this->resolveFromExpr($arg->value);
        }
        return null;
    }
    public function resolveFromExpr(Expr $targetEntityExpr) : ?string
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
