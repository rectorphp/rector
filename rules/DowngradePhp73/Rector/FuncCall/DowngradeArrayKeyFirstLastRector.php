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
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/array_key_first_last
 *
 * @see \Rector\Tests\DowngradePhp73\Rector\FuncCall\DowngradeArrayKeyFirstLastRector\DowngradeArrayKeyFirstLastRectorTest
 */
final class DowngradeArrayKeyFirstLastRector extends \Rector\Core\Rector\AbstractRector
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
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Downgrade array_key_first() and array_key_last() functions', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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
        return [\PhpParser\Node\Expr\FuncCall::class];
    }
    /**
     * @param FuncCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($this->isName($node, 'array_key_first')) {
            return $this->refactorArrayKeyFirst($node);
        }
        if ($this->isName($node, 'array_key_last')) {
            return $this->refactorArrayKeyLast($node);
        }
        return null;
    }
    private function refactorArrayKeyFirst(\PhpParser\Node\Expr\FuncCall $funcCall) : ?\PhpParser\Node\Expr\FuncCall
    {
        if (!isset($funcCall->args[0])) {
            return null;
        }
        if (!$funcCall->args[0] instanceof \PhpParser\Node\Arg) {
            return null;
        }
        $originalArray = $funcCall->args[0]->value;
        $array = $this->resolveCastedArray($originalArray);
        if ($originalArray !== $array) {
            $this->addAssignNewVariable($funcCall, $originalArray, $array);
        }
        $resetFuncCall = $this->nodeFactory->createFuncCall('reset', [$array]);
        $this->nodesToAddCollector->addNodeBeforeNode($resetFuncCall, $funcCall, $this->file->getSmartFileInfo());
        $funcCall->name = new \PhpParser\Node\Name('key');
        if ($originalArray !== $array) {
            $funcCall->args[0]->value = $array;
        }
        return $funcCall;
    }
    private function refactorArrayKeyLast(\PhpParser\Node\Expr\FuncCall $funcCall) : ?\PhpParser\Node\Expr\FuncCall
    {
        if (!isset($funcCall->args[0])) {
            return null;
        }
        if (!$funcCall->args[0] instanceof \PhpParser\Node\Arg) {
            return null;
        }
        $originalArray = $funcCall->args[0]->value;
        $array = $this->resolveCastedArray($originalArray);
        if ($originalArray !== $array) {
            $this->addAssignNewVariable($funcCall, $originalArray, $array);
        }
        $resetFuncCall = $this->nodeFactory->createFuncCall('end', [$array]);
        $this->nodesToAddCollector->addNodeBeforeNode($resetFuncCall, $funcCall, $this->file->getSmartFileInfo());
        $funcCall->name = new \PhpParser\Node\Name('key');
        if ($originalArray !== $array) {
            $funcCall->args[0]->value = $array;
        }
        return $funcCall;
    }
    /**
     * @param \PhpParser\Node\Expr|\PhpParser\Node\Expr\Variable $variable
     */
    private function addAssignNewVariable(\PhpParser\Node\Expr\FuncCall $funcCall, \PhpParser\Node\Expr $expr, $variable) : void
    {
        $this->nodesToAddCollector->addNodeBeforeNode(new \PhpParser\Node\Stmt\Expression(new \PhpParser\Node\Expr\Assign($variable, $expr)), $funcCall, $this->file->getSmartFileInfo());
    }
    /**
     * @return \PhpParser\Node\Expr|\PhpParser\Node\Expr\Variable
     */
    private function resolveCastedArray(\PhpParser\Node\Expr $expr)
    {
        if (!$expr instanceof \PhpParser\Node\Expr\Cast\Array_) {
            return $expr;
        }
        if ($expr->expr instanceof \PhpParser\Node\Expr\Cast\Array_) {
            return $this->resolveCastedArray($expr->expr);
        }
        $scope = $expr->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        $variableName = $this->variableNaming->createCountedValueName((string) $this->nodeNameResolver->getName($expr->expr), $scope);
        return new \PhpParser\Node\Expr\Variable($variableName);
    }
}
