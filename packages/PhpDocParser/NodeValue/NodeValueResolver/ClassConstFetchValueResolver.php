<?php

declare (strict_types=1);
namespace Rector\PhpDocParser\NodeValue\NodeValueResolver;

use PhpParser\ConstExprEvaluationException;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PHPStan\Reflection\ReflectionProvider;
use Rector\PhpDocParser\Contract\NodeValueResolver\NodeValueResolverInterface;
use ReflectionClassConstant;
/**
 * @see \Rector\Tests\PhpDocParser\NodeValue\NodeValueResolverTest
 *
 * @implements NodeValueResolverInterface<ClassConstFetch>
 */
final class ClassConstFetchValueResolver implements NodeValueResolverInterface
{
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    public function __construct(ReflectionProvider $reflectionProvider)
    {
        $this->reflectionProvider = $reflectionProvider;
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
        if (!$expr->class instanceof Name) {
            return null;
        }
        $className = $expr->class->toString();
        if ($className === 'self') {
            // unable to resolve
            throw new ConstExprEvaluationException('Unable to resolve self class constant');
        }
        if (!$expr->name instanceof Identifier) {
            return null;
        }
        $constantName = $expr->name->toString();
        if ($constantName === 'class') {
            return $className;
        }
        if (!$this->reflectionProvider->hasClass($className)) {
            return null;
        }
        $reflectionClassConstant = new ReflectionClassConstant($className, $constantName);
        return $reflectionClassConstant->getValue();
    }
}
