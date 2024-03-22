<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\Yield_;
use PhpParser\Node\Expr\YieldFrom;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\Throw_;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\Rector\AbstractRector;
use Rector\TypeDeclaration\TypeInferer\SilentVoidResolver;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\ClassMethod\ExplicitReturnNullRector\ExplicitReturnNullRectorTest
 */
final class ExplicitReturnNullRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\TypeInferer\SilentVoidResolver
     */
    private $silentVoidResolver;
    public function __construct(BetterNodeFinder $betterNodeFinder, SilentVoidResolver $silentVoidResolver)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->silentVoidResolver = $silentVoidResolver;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add explicit return null to method/function that returns a value, but missed main return', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run(int $number)
    {
        if ($number > 50) {
            return 'yes';
        }
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run(int $number)
    {
        if ($number > 50) {
            return 'yes';
        }

        return null;
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
        return [ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node) : ?Node
    {
        // known return type, nothing to improve
        if ($node->returnType instanceof Node) {
            return null;
        }
        if ($this->containsYieldOrThrow($node)) {
            return null;
        }
        // it has at least some return value in it
        if (!$this->hasReturnsWithValues($node)) {
            return null;
        }
        if (!$this->silentVoidResolver->hasSilentVoid($node)) {
            return null;
        }
        $node->stmts[] = new Return_(new ConstFetch(new Name('null')));
        return $node;
    }
    private function containsYieldOrThrow(ClassMethod $classMethod) : bool
    {
        return (bool) $this->betterNodeFinder->findInstancesOf($classMethod, [Yield_::class, Throw_::class, Node\Expr\Throw_::class, YieldFrom::class]);
    }
    private function hasReturnsWithValues(ClassMethod $classMethod) : bool
    {
        /** @var Return_[] $returns */
        $returns = $this->betterNodeFinder->findInstancesOfInFunctionLikeScoped($classMethod, Return_::class);
        foreach ($returns as $return) {
            if (!$return->expr instanceof Node) {
                return \false;
            }
        }
        return $returns !== [];
    }
}
