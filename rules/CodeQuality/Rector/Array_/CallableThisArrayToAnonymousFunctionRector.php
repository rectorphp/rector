<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\Array_;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\Property;
use PhpParser\NodeTraverser;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\Php\PhpMethodReflection;
use Rector\Configuration\Deprecation\Contract\DeprecatedInterface;
use Rector\NodeCollector\NodeAnalyzer\ArrayCallableMethodMatcher;
use Rector\NodeCollector\ValueObject\ArrayCallable;
use Rector\Php72\NodeFactory\AnonymousFunctionFactory;
use Rector\Rector\AbstractScopeAwareRector;
use Rector\Reflection\ReflectionResolver;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\Array_\CallableThisArrayToAnonymousFunctionRector\CallableThisArrayToAnonymousFunctionRectorTest
 *
 * @deprecated This rule is surpassed by more advanced one
 * Use @see FirstClassCallableRector instead
 */
final class CallableThisArrayToAnonymousFunctionRector extends AbstractScopeAwareRector implements DeprecatedInterface
{
    /**
     * @readonly
     * @var \Rector\Php72\NodeFactory\AnonymousFunctionFactory
     */
    private $anonymousFunctionFactory;
    /**
     * @readonly
     * @var \Rector\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    /**
     * @readonly
     * @var \Rector\NodeCollector\NodeAnalyzer\ArrayCallableMethodMatcher
     */
    private $arrayCallableMethodMatcher;
    public function __construct(AnonymousFunctionFactory $anonymousFunctionFactory, ReflectionResolver $reflectionResolver, ArrayCallableMethodMatcher $arrayCallableMethodMatcher)
    {
        $this->anonymousFunctionFactory = $anonymousFunctionFactory;
        $this->reflectionResolver = $reflectionResolver;
        $this->arrayCallableMethodMatcher = $arrayCallableMethodMatcher;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Convert [$this, "method"] to proper anonymous function', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $values = [1, 5, 3];
        usort($values, [$this, 'compareSize']);

        return $values;
    }

    private function compareSize($first, $second)
    {
        return $first <=> $second;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $values = [1, 5, 3];
        usort($values, function ($first, $second) {
            return $this->compareSize($first, $second);
        });

        return $values;
    }

    private function compareSize($first, $second)
    {
        return $first <=> $second;
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Property::class, ClassConst::class, Array_::class];
    }
    /**
     * @param Property|ClassConst|Array_ $node
     * @return null|int|\PhpParser\Node
     */
    public function refactorWithScope(Node $node, Scope $scope)
    {
        if ($node instanceof Property || $node instanceof ClassConst) {
            return NodeTraverser::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
        }
        if ($this->shouldSkipTwigExtension($scope)) {
            return null;
        }
        $arrayCallable = $this->arrayCallableMethodMatcher->match($node, $scope);
        if (!$arrayCallable instanceof ArrayCallable) {
            return null;
        }
        $phpMethodReflection = $this->reflectionResolver->resolveMethodReflection($arrayCallable->getClass(), $arrayCallable->getMethod(), $scope);
        if (!$phpMethodReflection instanceof PhpMethodReflection) {
            return null;
        }
        return $this->anonymousFunctionFactory->createFromPhpMethodReflection($phpMethodReflection, $arrayCallable->getCallerExpr());
    }
    private function shouldSkipTwigExtension(Scope $scope) : bool
    {
        if (!$scope->isInClass()) {
            return \false;
        }
        $classReflection = $scope->getClassReflection();
        return $classReflection->isSubclassOf('Twig\\Extension\\ExtensionInterface');
    }
}
