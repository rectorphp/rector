<?php

declare (strict_types=1);
namespace RectorPrefix20210514\Symplify\Astral\NodeValue;

use PhpParser\ConstExprEvaluationException;
use PhpParser\ConstExprEvaluator;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Cast;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\MagicConst;
use PhpParser\Node\Scalar\MagicConst\Dir;
use PhpParser\Node\Scalar\MagicConst\File;
use PhpParser\Node\Stmt\ClassLike;
use ReflectionClassConstant;
use RectorPrefix20210514\Symplify\Astral\Naming\SimpleNameResolver;
use RectorPrefix20210514\Symplify\Astral\NodeFinder\SimpleNodeFinder;
use RectorPrefix20210514\Symplify\PackageBuilder\Php\TypeChecker;
/**
 * @see \Symplify\Astral\Tests\NodeValue\NodeValueResolverTest
 */
final class NodeValueResolver
{
    /**
     * @var ConstExprEvaluator
     */
    private $constExprEvaluator;
    /**
     * @var SimpleNameResolver
     */
    private $simpleNameResolver;
    /**
     * @var TypeChecker
     */
    private $typeChecker;
    /**
     * @var string
     */
    private $currentFilePath;
    /**
     * @var SimpleNodeFinder
     */
    private $simpleNodeFinder;
    public function __construct(\RectorPrefix20210514\Symplify\Astral\Naming\SimpleNameResolver $simpleNameResolver, \RectorPrefix20210514\Symplify\PackageBuilder\Php\TypeChecker $typeChecker, \RectorPrefix20210514\Symplify\Astral\NodeFinder\SimpleNodeFinder $simpleNodeFinder)
    {
        $this->simpleNameResolver = $simpleNameResolver;
        $this->constExprEvaluator = new \PhpParser\ConstExprEvaluator(function (\PhpParser\Node\Expr $expr) {
            return $this->resolveByNode($expr);
        });
        $this->typeChecker = $typeChecker;
        $this->simpleNodeFinder = $simpleNodeFinder;
    }
    /**
     * @return array|bool|float|int|mixed|string|null
     */
    public function resolve(\PhpParser\Node\Expr $expr, string $filePath)
    {
        $this->currentFilePath = $filePath;
        try {
            return $this->constExprEvaluator->evaluateDirectly($expr);
        } catch (\PhpParser\ConstExprEvaluationException $constExprEvaluationException) {
            return null;
        }
    }
    /**
     * @return mixed|null
     */
    private function resolveClassConstFetch(\PhpParser\Node\Expr\ClassConstFetch $classConstFetch)
    {
        $className = $this->simpleNameResolver->getName($classConstFetch->class);
        if ($className === 'self') {
            $classLike = $this->simpleNodeFinder->findFirstParentByType($classConstFetch, \PhpParser\Node\Stmt\ClassLike::class);
            if (!$classLike instanceof \PhpParser\Node\Stmt\ClassLike) {
                return null;
            }
            $className = $this->simpleNameResolver->getName($classLike);
        }
        if ($className === null) {
            return null;
        }
        $constantName = $this->simpleNameResolver->getName($classConstFetch->name);
        if ($constantName === null) {
            return null;
        }
        if ($constantName === 'class') {
            return $className;
        }
        $reflectionClassConstant = new \ReflectionClassConstant($className, $constantName);
        return $reflectionClassConstant->getValue();
    }
    private function resolveMagicConst(\PhpParser\Node\Scalar\MagicConst $magicConst) : ?string
    {
        if ($magicConst instanceof \PhpParser\Node\Scalar\MagicConst\Dir) {
            return \dirname($this->currentFilePath);
        }
        if ($magicConst instanceof \PhpParser\Node\Scalar\MagicConst\File) {
            return $this->currentFilePath;
        }
        return null;
    }
    /**
     * @return mixed|null
     */
    private function resolveConstFetch(\PhpParser\Node\Expr\ConstFetch $constFetch)
    {
        $constFetchName = $this->simpleNameResolver->getName($constFetch);
        if ($constFetchName === null) {
            return null;
        }
        return \constant($constFetchName);
    }
    /**
     * @return mixed|string|int|bool|null
     */
    private function resolveByNode(\PhpParser\Node\Expr $expr)
    {
        if ($expr instanceof \PhpParser\Node\Scalar\MagicConst) {
            return $this->resolveMagicConst($expr);
        }
        if ($expr instanceof \PhpParser\Node\Expr\FuncCall && $this->simpleNameResolver->isName($expr, 'getcwd')) {
            return \dirname($this->currentFilePath);
        }
        if ($expr instanceof \PhpParser\Node\Expr\ConstFetch) {
            return $this->resolveConstFetch($expr);
        }
        if ($expr instanceof \PhpParser\Node\Expr\ClassConstFetch) {
            return $this->resolveClassConstFetch($expr);
        }
        if ($this->typeChecker->isInstanceOf($expr, [\PhpParser\Node\Expr\Variable::class, \PhpParser\Node\Expr\Cast::class, \PhpParser\Node\Expr\MethodCall::class, \PhpParser\Node\Expr\PropertyFetch::class, \PhpParser\Node\Expr\Instanceof_::class])) {
            throw new \PhpParser\ConstExprEvaluationException();
        }
        return null;
    }
}
