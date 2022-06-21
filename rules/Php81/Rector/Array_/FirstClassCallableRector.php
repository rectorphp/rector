<?php

declare (strict_types=1);
namespace Rector\Php81\Rector\Array_;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\VariadicPlaceholder;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersion;
use Rector\NodeCollector\NodeAnalyzer\ArrayCallableMethodMatcher;
use Rector\NodeCollector\ValueObject\ArrayCallable;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://php.watch/versions/8.1/first-class-callable-syntax
 *
 * @see \Rector\Tests\Php81\Rector\Array_\FirstClassCallableRector\FirstClassCallableRectorTest
 */
final class FirstClassCallableRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\NodeCollector\NodeAnalyzer\ArrayCallableMethodMatcher
     */
    private $arrayCallableMethodMatcher;
    public function __construct(ArrayCallableMethodMatcher $arrayCallableMethodMatcher)
    {
        $this->arrayCallableMethodMatcher = $arrayCallableMethodMatcher;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Upgrade array callable to first class callable', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public function run()
    {
        $name = [$this, 'name'];
    }

    public function name()
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    public function run()
    {
        $name = $this->name(...);
    }

    public function name()
    {
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
        return [Array_::class];
    }
    /**
     * @param Array_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        $arrayCallable = $this->arrayCallableMethodMatcher->match($node);
        if (!$arrayCallable instanceof ArrayCallable) {
            return null;
        }
        $callerExpr = $arrayCallable->getCallerExpr();
        if (!$callerExpr instanceof Variable && !$callerExpr instanceof PropertyFetch && !$callerExpr instanceof ClassConstFetch) {
            return null;
        }
        $args = [new VariadicPlaceholder()];
        if ($callerExpr instanceof ClassConstFetch) {
            return new StaticCall($callerExpr->class, $arrayCallable->getMethod(), $args);
        }
        return new MethodCall($callerExpr, $arrayCallable->getMethod(), $args);
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersion::PHP_81;
    }
}
