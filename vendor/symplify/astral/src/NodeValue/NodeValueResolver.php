<?php

declare (strict_types=1);
namespace RectorPrefix20220501\Symplify\Astral\NodeValue;

use PhpParser\ConstExprEvaluationException;
use PhpParser\ConstExprEvaluator;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Cast;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PHPStan\Analyser\Scope;
use PHPStan\Type\ConstantScalarType;
use PHPStan\Type\UnionType;
use RectorPrefix20220501\Symplify\Astral\Contract\NodeValueResolver\NodeValueResolverInterface;
use RectorPrefix20220501\Symplify\Astral\Exception\ShouldNotHappenException;
use RectorPrefix20220501\Symplify\Astral\Naming\SimpleNameResolver;
use RectorPrefix20220501\Symplify\Astral\NodeFinder\SimpleNodeFinder;
use RectorPrefix20220501\Symplify\Astral\NodeValue\NodeValueResolver\ClassConstFetchValueResolver;
use RectorPrefix20220501\Symplify\Astral\NodeValue\NodeValueResolver\ConstFetchValueResolver;
use RectorPrefix20220501\Symplify\Astral\NodeValue\NodeValueResolver\FuncCallValueResolver;
use RectorPrefix20220501\Symplify\Astral\NodeValue\NodeValueResolver\MagicConstValueResolver;
use RectorPrefix20220501\Symplify\PackageBuilder\Php\TypeChecker;
/**
 * @see \Symplify\Astral\Tests\NodeValue\NodeValueResolverTest
 */
final class NodeValueResolver
{
    /**
     * @var \PhpParser\ConstExprEvaluator
     */
    private $constExprEvaluator;
    /**
     * @var string|null
     */
    private $currentFilePath;
    /**
     * @var \Symplify\Astral\NodeValue\UnionTypeValueResolver
     */
    private $unionTypeValueResolver;
    /**
     * @var array<NodeValueResolverInterface>
     */
    private $nodeValueResolvers = [];
    /**
     * @var \Symplify\Astral\Naming\SimpleNameResolver
     */
    private $simpleNameResolver;
    /**
     * @var \Symplify\PackageBuilder\Php\TypeChecker
     */
    private $typeChecker;
    public function __construct(\RectorPrefix20220501\Symplify\Astral\Naming\SimpleNameResolver $simpleNameResolver, \RectorPrefix20220501\Symplify\PackageBuilder\Php\TypeChecker $typeChecker, \RectorPrefix20220501\Symplify\Astral\NodeFinder\SimpleNodeFinder $simpleNodeFinder)
    {
        $this->simpleNameResolver = $simpleNameResolver;
        $this->typeChecker = $typeChecker;
        $this->constExprEvaluator = new \PhpParser\ConstExprEvaluator(function (\PhpParser\Node\Expr $expr) {
            return $this->resolveByNode($expr);
        });
        $this->unionTypeValueResolver = new \RectorPrefix20220501\Symplify\Astral\NodeValue\UnionTypeValueResolver();
        $this->nodeValueResolvers[] = new \RectorPrefix20220501\Symplify\Astral\NodeValue\NodeValueResolver\ClassConstFetchValueResolver($this->simpleNameResolver, $simpleNodeFinder);
        $this->nodeValueResolvers[] = new \RectorPrefix20220501\Symplify\Astral\NodeValue\NodeValueResolver\ConstFetchValueResolver($this->simpleNameResolver);
        $this->nodeValueResolvers[] = new \RectorPrefix20220501\Symplify\Astral\NodeValue\NodeValueResolver\MagicConstValueResolver();
        $this->nodeValueResolvers[] = new \RectorPrefix20220501\Symplify\Astral\NodeValue\NodeValueResolver\FuncCallValueResolver($this->simpleNameResolver, $this->constExprEvaluator);
    }
    /**
     * @return mixed
     */
    public function resolveWithScope(\PhpParser\Node\Expr $expr, \PHPStan\Analyser\Scope $scope)
    {
        $this->currentFilePath = $scope->getFile();
        try {
            return $this->constExprEvaluator->evaluateDirectly($expr);
        } catch (\PhpParser\ConstExprEvaluationException $exception) {
        }
        $exprType = $scope->getType($expr);
        if ($exprType instanceof \PHPStan\Type\ConstantScalarType) {
            return $exprType->getValue();
        }
        if ($exprType instanceof \PHPStan\Type\UnionType) {
            return $this->unionTypeValueResolver->resolveConstantTypes($exprType);
        }
        return null;
    }
    /**
     * @return mixed
     */
    public function resolve(\PhpParser\Node\Expr $expr, string $filePath)
    {
        $this->currentFilePath = $filePath;
        try {
            return $this->constExprEvaluator->evaluateDirectly($expr);
        } catch (\PhpParser\ConstExprEvaluationException $exception) {
            return null;
        }
    }
    /**
     * @return mixed
     */
    private function resolveByNode(\PhpParser\Node\Expr $expr)
    {
        if ($this->currentFilePath === null) {
            throw new \RectorPrefix20220501\Symplify\Astral\Exception\ShouldNotHappenException();
        }
        foreach ($this->nodeValueResolvers as $nodeValueResolver) {
            if (\is_a($expr, $nodeValueResolver->getType(), \true)) {
                return $nodeValueResolver->resolve($expr, $this->currentFilePath);
            }
        }
        // these values cannot be resolved in reliable way
        if ($this->typeChecker->isInstanceOf($expr, [\PhpParser\Node\Expr\Variable::class, \PhpParser\Node\Expr\Cast::class, \PhpParser\Node\Expr\MethodCall::class, \PhpParser\Node\Expr\PropertyFetch::class, \PhpParser\Node\Expr\Instanceof_::class])) {
            throw new \PhpParser\ConstExprEvaluationException();
        }
        return null;
    }
}
