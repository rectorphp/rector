<?php

declare (strict_types=1);
namespace Rector\DowngradePhp71\Rector\StaticCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\ClosureUse;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Echo_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\Switch_;
use Rector\Core\Contract\PhpParser\Node\StmtsAwareInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeAnalyzer\ExprInTopStmtMatcher;
use Rector\NodeFactory\NamedVariableFactory;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/closurefromcallable
 *
 * @see \Rector\Tests\DowngradePhp71\Rector\StaticCall\DowngradeClosureFromCallableRector\DowngradeClosureFromCallableRectorTest
 */
final class DowngradeClosureFromCallableRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\NodeFactory\NamedVariableFactory
     */
    private $namedVariableFactory;
    /**
     * @readonly
     * @var \Rector\NodeAnalyzer\ExprInTopStmtMatcher
     */
    private $exprInTopStmtMatcher;
    public function __construct(NamedVariableFactory $namedVariableFactory, ExprInTopStmtMatcher $exprInTopStmtMatcher)
    {
        $this->namedVariableFactory = $namedVariableFactory;
        $this->exprInTopStmtMatcher = $exprInTopStmtMatcher;
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [StmtsAwareInterface::class, Switch_::class, Return_::class, Expression::class, Echo_::class];
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Converts Closure::fromCallable() to compatible alternative.', [new CodeSample(<<<'CODE_SAMPLE'
$someClosure = \Closure::fromCallable('callable');
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$callable = 'callable';
$someClosure = function () use ($callable) {
    return $callable(...func_get_args());
};
CODE_SAMPLE
)]);
    }
    /**
     * @param StmtsAwareInterface|Switch_|Return_|Expression|Echo_ $node
     * @return Node[]|null
     */
    public function refactor(Node $node) : ?array
    {
        $expr = $this->exprInTopStmtMatcher->match($node, function (Node $subNode) : bool {
            if (!$subNode instanceof StaticCall) {
                return \false;
            }
            if ($this->shouldSkipStaticCall($subNode)) {
                return \false;
            }
            $args = $subNode->getArgs();
            return isset($args[0]);
        });
        if (!$expr instanceof StaticCall) {
            return null;
        }
        /** @var Stmt $node */
        $tempVariable = $this->namedVariableFactory->createVariable('callable', $node);
        $assignExpression = new Expression(new Assign($tempVariable, $expr->getArgs()[0]->value));
        $innerFuncCall = new FuncCall($tempVariable, [new Arg($this->nodeFactory->createFuncCall('func_get_args'), \false, \true)]);
        $closure = new Closure();
        $closure->uses[] = new ClosureUse($tempVariable);
        $closure->stmts[] = new Return_($innerFuncCall);
        $this->traverseNodesWithCallable($node, static function (Node $subNode) use($expr, $closure) {
            if ($subNode === $expr) {
                return $closure;
            }
        });
        return [$assignExpression, $node];
    }
    private function shouldSkipStaticCall(StaticCall $staticCall) : bool
    {
        if (!$this->nodeNameResolver->isName($staticCall->class, 'Closure')) {
            return \true;
        }
        if (!$this->nodeNameResolver->isName($staticCall->name, 'fromCallable')) {
            return \true;
        }
        return !isset($staticCall->args[0]);
    }
}
