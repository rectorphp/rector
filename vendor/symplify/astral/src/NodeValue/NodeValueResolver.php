<?php

declare (strict_types=1);
namespace RectorPrefix202208\Symplify\Astral\NodeValue;

use PhpParser\ConstExprEvaluationException;
use PhpParser\ConstExprEvaluator;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Cast;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use RectorPrefix202208\Symplify\Astral\Contract\NodeValueResolver\NodeValueResolverInterface;
use RectorPrefix202208\Symplify\Astral\Exception\ShouldNotHappenException;
use RectorPrefix202208\Symplify\Astral\Naming\SimpleNameResolver;
use RectorPrefix202208\Symplify\Astral\NodeValue\NodeValueResolver\ClassConstFetchValueResolver;
use RectorPrefix202208\Symplify\Astral\NodeValue\NodeValueResolver\ConstFetchValueResolver;
use RectorPrefix202208\Symplify\Astral\NodeValue\NodeValueResolver\FuncCallValueResolver;
use RectorPrefix202208\Symplify\Astral\NodeValue\NodeValueResolver\MagicConstValueResolver;
use RectorPrefix202208\Symplify\PackageBuilder\Php\TypeChecker;
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
     * @var NodeValueResolverInterface[]
     */
    private $nodeValueResolvers = [];
    /**
     * @var \Symplify\PackageBuilder\Php\TypeChecker
     */
    private $typeChecker;
    public function __construct(SimpleNameResolver $simpleNameResolver, TypeChecker $typeChecker)
    {
        $this->typeChecker = $typeChecker;
        $this->constExprEvaluator = new ConstExprEvaluator(function (Expr $expr) {
            return $this->resolveByNode($expr);
        });
        $this->nodeValueResolvers[] = new ClassConstFetchValueResolver($simpleNameResolver);
        $this->nodeValueResolvers[] = new ConstFetchValueResolver($simpleNameResolver);
        $this->nodeValueResolvers[] = new MagicConstValueResolver();
        $this->nodeValueResolvers[] = new FuncCallValueResolver($simpleNameResolver, $this->constExprEvaluator);
    }
    /**
     * @return mixed
     */
    public function resolve(Expr $expr, string $filePath)
    {
        $this->currentFilePath = $filePath;
        try {
            return $this->constExprEvaluator->evaluateDirectly($expr);
        } catch (ConstExprEvaluationException $exception) {
            return null;
        }
    }
    /**
     * @return mixed
     */
    private function resolveByNode(Expr $expr)
    {
        if ($this->currentFilePath === null) {
            throw new ShouldNotHappenException();
        }
        foreach ($this->nodeValueResolvers as $nodeValueResolver) {
            if (\is_a($expr, $nodeValueResolver->getType(), \true)) {
                return $nodeValueResolver->resolve($expr, $this->currentFilePath);
            }
        }
        // these values cannot be resolved in reliable way
        if ($this->typeChecker->isInstanceOf($expr, [Variable::class, Cast::class, MethodCall::class, PropertyFetch::class, Instanceof_::class])) {
            throw new ConstExprEvaluationException();
        }
        return null;
    }
}
