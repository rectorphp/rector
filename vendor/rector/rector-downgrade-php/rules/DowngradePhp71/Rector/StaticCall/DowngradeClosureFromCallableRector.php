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
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeFactory\NamedVariableFactory;
use Rector\PostRector\Collector\NodesToAddCollector;
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
     * @var \Rector\PostRector\Collector\NodesToAddCollector
     */
    private $nodesToAddCollector;
    public function __construct(NamedVariableFactory $namedVariableFactory, NodesToAddCollector $nodesToAddCollector)
    {
        $this->namedVariableFactory = $namedVariableFactory;
        $this->nodesToAddCollector = $nodesToAddCollector;
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [StaticCall::class];
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Converts Closure::fromCallable() to compatible alternative.', [new CodeSample(<<<'CODE_SAMPLE'
\Closure::fromCallable('callable');
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$callable = 'callable';
function () use ($callable) {
    return $callable(...func_get_args());
};
CODE_SAMPLE
)]);
    }
    /**
     * @param StaticCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }
        if (!isset($node->args[0])) {
            return null;
        }
        if (!$node->args[0] instanceof Arg) {
            return null;
        }
        $tempVariable = $this->namedVariableFactory->createVariable($node, 'callable');
        $expression = new Expression(new Assign($tempVariable, $node->args[0]->value));
        $this->nodesToAddCollector->addNodeBeforeNode($expression, $node);
        $closure = new Closure();
        $closure->uses[] = new ClosureUse($tempVariable);
        $innerFuncCall = new FuncCall($tempVariable, [new Arg($this->nodeFactory->createFuncCall('func_get_args'), \false, \true)]);
        $closure->stmts[] = new Return_($innerFuncCall);
        return $closure;
    }
    private function shouldSkip(StaticCall $staticCall) : bool
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
