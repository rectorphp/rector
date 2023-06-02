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
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\Rector\AbstractRector;
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
    public function __construct(NamedVariableFactory $namedVariableFactory)
    {
        $this->namedVariableFactory = $namedVariableFactory;
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Expression::class, Return_::class];
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
     * @param Expression|Return_ $node
     * @return Stmt[]|null
     */
    public function refactor(Node $node) : ?array
    {
        if ($node instanceof Return_) {
            return $this->refactorReturn($node);
        }
        if (!$node->expr instanceof Assign) {
            return null;
        }
        $assign = $node->expr;
        if (!$assign->expr instanceof StaticCall) {
            return null;
        }
        $staticCall = $assign->expr;
        if ($this->shouldSkipStaticCall($staticCall)) {
            return null;
        }
        $args = $staticCall->getArgs();
        if (!isset($args[0])) {
            return null;
        }
        $tempVariable = $this->namedVariableFactory->createVariable($staticCall, 'callable');
        $assignExpression = new Expression(new Assign($tempVariable, $args[0]->value));
        $innerFuncCall = new FuncCall($tempVariable, [new Arg($this->nodeFactory->createFuncCall('func_get_args'), \false, \true)]);
        $closure = new Closure();
        $closure->uses[] = new ClosureUse($tempVariable);
        $closure->stmts[] = new Return_($innerFuncCall);
        $assign->expr = $closure;
        return [$assignExpression, new Expression($assign)];
    }
    /**
     * @return Stmt[]|null
     */
    private function refactorReturn(Return_ $return) : ?array
    {
        if (!$return->expr instanceof StaticCall) {
            return null;
        }
        $staticCall = $return->expr;
        if ($this->shouldSkipStaticCall($staticCall)) {
            return null;
        }
        $args = $staticCall->getArgs();
        if (!isset($args[0])) {
            return null;
        }
        $tempVariable = $this->namedVariableFactory->createVariable($staticCall, 'callable');
        $assignExpression = new Expression(new Assign($tempVariable, $args[0]->value));
        $innerFuncCall = new FuncCall($tempVariable, [new Arg($this->nodeFactory->createFuncCall('func_get_args'), \false, \true)]);
        $closure = new Closure();
        $closure->uses[] = new ClosureUse($tempVariable);
        $closure->stmts[] = new Return_($innerFuncCall);
        $return->expr = $closure;
        return [$assignExpression, $return];
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
