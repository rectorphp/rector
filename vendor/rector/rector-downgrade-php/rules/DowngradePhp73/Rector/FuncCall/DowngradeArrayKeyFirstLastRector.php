<?php

declare (strict_types=1);
namespace Rector\DowngradePhp73\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Cast\Array_;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Expression;
use Rector\Core\Rector\AbstractRector;
use Rector\Naming\Naming\VariableNaming;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PostRector\Collector\NodesToAddCollector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/array_key_first_last
 *
 * @see \Rector\Tests\DowngradePhp73\Rector\FuncCall\DowngradeArrayKeyFirstLastRector\DowngradeArrayKeyFirstLastRectorTest
 */
final class DowngradeArrayKeyFirstLastRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Naming\Naming\VariableNaming
     */
    private $variableNaming;
    /**
     * @readonly
     * @var \Rector\PostRector\Collector\NodesToAddCollector
     */
    private $nodesToAddCollector;
    public function __construct(VariableNaming $variableNaming, NodesToAddCollector $nodesToAddCollector)
    {
        $this->variableNaming = $variableNaming;
        $this->nodesToAddCollector = $nodesToAddCollector;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Downgrade array_key_first() and array_key_last() functions', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run($items)
    {
        $firstItemKey = array_key_first($items);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run($items)
    {
        reset($items);
        $firstItemKey = key($items);
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
        return [FuncCall::class];
    }
    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($this->isName($node, 'array_key_first')) {
            return $this->refactorArrayKeyFirst($node);
        }
        if ($this->isName($node, 'array_key_last')) {
            return $this->refactorArrayKeyLast($node);
        }
        return null;
    }
    private function refactorArrayKeyFirst(FuncCall $funcCall) : ?FuncCall
    {
        if (!isset($funcCall->args[0])) {
            return null;
        }
        if (!$funcCall->args[0] instanceof Arg) {
            return null;
        }
        $originalArray = $funcCall->args[0]->value;
        $array = $this->resolveCastedArray($originalArray);
        if ($originalArray !== $array) {
            $this->addAssignNewVariable($funcCall, $originalArray, $array);
        }
        $resetFuncCall = $this->nodeFactory->createFuncCall('reset', [$array]);
        $this->nodesToAddCollector->addNodeBeforeNode($resetFuncCall, $funcCall);
        $funcCall->name = new Name('key');
        if ($originalArray !== $array) {
            $firstArg = $funcCall->getArgs()[0];
            $firstArg->value = $array;
        }
        return $funcCall;
    }
    private function refactorArrayKeyLast(FuncCall $funcCall) : ?FuncCall
    {
        if (!isset($funcCall->args[0])) {
            return null;
        }
        if (!$funcCall->args[0] instanceof Arg) {
            return null;
        }
        $originalArray = $funcCall->args[0]->value;
        $array = $this->resolveCastedArray($originalArray);
        if ($originalArray !== $array) {
            $this->addAssignNewVariable($funcCall, $originalArray, $array);
        }
        $resetFuncCall = $this->nodeFactory->createFuncCall('end', [$array]);
        $this->nodesToAddCollector->addNodeBeforeNode($resetFuncCall, $funcCall);
        $funcCall->name = new Name('key');
        if ($originalArray !== $array) {
            $firstArg = $funcCall->getArgs()[0];
            $firstArg->value = $array;
        }
        return $funcCall;
    }
    /**
     * @param \PhpParser\Node\Expr|\PhpParser\Node\Expr\Variable $variable
     */
    private function addAssignNewVariable(FuncCall $funcCall, Expr $expr, $variable) : void
    {
        $this->nodesToAddCollector->addNodeBeforeNode(new Expression(new Assign($variable, $expr)), $funcCall);
    }
    /**
     * @return \PhpParser\Node\Expr|\PhpParser\Node\Expr\Variable
     */
    private function resolveCastedArray(Expr $expr)
    {
        if (!$expr instanceof Array_) {
            return $expr;
        }
        if ($expr->expr instanceof Array_) {
            return $this->resolveCastedArray($expr->expr);
        }
        $scope = $expr->getAttribute(AttributeKey::SCOPE);
        $variableName = $this->variableNaming->createCountedValueName((string) $this->nodeNameResolver->getName($expr->expr), $scope);
        return new Variable($variableName);
    }
}
