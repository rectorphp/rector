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
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\Rector\AbstractRector;
use Rector\Naming\Naming\VariableNaming;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/closurefromcallable
 *
 * @see \Rector\Tests\DowngradePhp71\Rector\StaticCall\DowngradeClosureFromCallableRector\DowngradeClosureFromCallableRectorTest
 */
final class DowngradeClosureFromCallableRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Naming\Naming\VariableNaming
     */
    private $variableNaming;
    public function __construct(\Rector\Naming\Naming\VariableNaming $variableNaming)
    {
        $this->variableNaming = $variableNaming;
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\StaticCall::class];
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Converts Closure::fromCallable() to compatible alternative.', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }
        if (!isset($node->args[0])) {
            return null;
        }
        if (!$node->args[0] instanceof \PhpParser\Node\Arg) {
            return null;
        }
        $tempVariableName = $this->variableNaming->createCountedValueName('callable', $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE));
        $tempVariable = new \PhpParser\Node\Expr\Variable($tempVariableName);
        $expression = new \PhpParser\Node\Stmt\Expression(new \PhpParser\Node\Expr\Assign($tempVariable, $node->args[0]->value));
        $currentStatement = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CURRENT_STATEMENT);
        $this->nodesToAddCollector->addNodeBeforeNode($expression, $currentStatement);
        $closure = new \PhpParser\Node\Expr\Closure();
        $closure->uses[] = new \PhpParser\Node\Expr\ClosureUse($tempVariable);
        $innerFuncCall = new \PhpParser\Node\Expr\FuncCall($tempVariable, [new \PhpParser\Node\Arg($this->nodeFactory->createFuncCall('func_get_args'), \false, \true)]);
        $closure->stmts[] = new \PhpParser\Node\Stmt\Return_($innerFuncCall);
        return $closure;
    }
    private function shouldSkip(\PhpParser\Node\Expr\StaticCall $staticCall) : bool
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
