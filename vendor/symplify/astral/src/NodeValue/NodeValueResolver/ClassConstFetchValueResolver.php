<?php

declare (strict_types=1);
namespace RectorPrefix202208\Symplify\Astral\NodeValue\NodeValueResolver;

use PhpParser\ConstExprEvaluationException;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ClassConstFetch;
use ReflectionClassConstant;
use RectorPrefix202208\Symplify\Astral\Contract\NodeValueResolver\NodeValueResolverInterface;
use RectorPrefix202208\Symplify\Astral\Naming\SimpleNameResolver;
/**
 * @see \Symplify\Astral\Tests\NodeValue\NodeValueResolverTest
 *
 * @implements NodeValueResolverInterface<ClassConstFetch>
 */
final class ClassConstFetchValueResolver implements NodeValueResolverInterface
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
        return ClassConstFetch::class;
    }
    /**
     * @param ClassConstFetch $expr
     * @return mixed
     */
    public function resolve(Expr $expr, string $currentFilePath)
    {
        $className = $this->simpleNameResolver->getName($expr->class);
        if ($className === 'self') {
            // unable to resolve
            throw new ConstExprEvaluationException('Unable to resolve self class constant');
        }
        if ($className === null) {
            return null;
        }
        $constantName = $this->simpleNameResolver->getName($expr->name);
        if ($constantName === null) {
            return null;
        }
        if ($constantName === 'class') {
            return $className;
        }
        if (!\class_exists($className) && !\interface_exists($className)) {
            return null;
        }
        $reflectionClassConstant = new ReflectionClassConstant($className, $constantName);
        return $reflectionClassConstant->getValue();
    }
}
