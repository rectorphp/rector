<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\Array_;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PHPStan\Reflection\Php\PhpMethodReflection;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\Reflection\ReflectionResolver;
use Rector\NodeCollector\NodeAnalyzer\ArrayCallableMethodMatcher;
use Rector\NodeCollector\ValueObject\ArrayCallable;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Php72\NodeFactory\AnonymousFunctionFactory;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://www.php.net/manual/en/language.types.callable.php#117260
 * @see https://3v4l.org/MsMbQ
 * @see https://3v4l.org/KM1Ji
 *
 * @see \Rector\Tests\CodeQuality\Rector\Array_\CallableThisArrayToAnonymousFunctionRector\CallableThisArrayToAnonymousFunctionRectorTest
 */
final class CallableThisArrayToAnonymousFunctionRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var \Rector\Php72\NodeFactory\AnonymousFunctionFactory
     */
    private $anonymousFunctionFactory;
    /**
     * @var \Rector\Core\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    /**
     * @var \Rector\NodeCollector\NodeAnalyzer\ArrayCallableMethodMatcher
     */
    private $arrayCallableMethodMatcher;
    public function __construct(\Rector\Php72\NodeFactory\AnonymousFunctionFactory $anonymousFunctionFactory, \Rector\Core\Reflection\ReflectionResolver $reflectionResolver, \Rector\NodeCollector\NodeAnalyzer\ArrayCallableMethodMatcher $arrayCallableMethodMatcher)
    {
        $this->anonymousFunctionFactory = $anonymousFunctionFactory;
        $this->reflectionResolver = $reflectionResolver;
        $this->arrayCallableMethodMatcher = $arrayCallableMethodMatcher;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Convert [$this, "method"] to proper anonymous function', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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
        return [\PhpParser\Node\Expr\Array_::class];
    }
    /**
     * @param Array_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $arrayCallable = $this->arrayCallableMethodMatcher->match($node);
        if (!$arrayCallable instanceof \Rector\NodeCollector\ValueObject\ArrayCallable) {
            return null;
        }
        $scope = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        $phpMethodReflection = $this->reflectionResolver->resolveMethodReflection($arrayCallable->getClass(), $arrayCallable->getMethod(), $scope);
        if (!$phpMethodReflection instanceof \PHPStan\Reflection\Php\PhpMethodReflection) {
            return null;
        }
        return $this->anonymousFunctionFactory->createFromPhpMethodReflection($phpMethodReflection, $arrayCallable->getCallerExpr());
    }
}
