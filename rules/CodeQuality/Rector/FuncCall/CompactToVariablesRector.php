<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Stmt;
use PHPStan\Analyser\Scope;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\MixedType;
use Rector\CodeQuality\CompactConverter;
use Rector\CodeQuality\NodeAnalyzer\ArrayCompacter;
use Rector\CodeQuality\NodeAnalyzer\ArrayItemsAnalyzer;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://stackoverflow.com/a/16319909/1348344
 * @see https://3v4l.org/8GJEs
 * @see \Rector\Tests\CodeQuality\Rector\FuncCall\CompactToVariablesRector\CompactToVariablesRectorTest
 */
final class CompactToVariablesRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @readonly
     * @var \Rector\CodeQuality\CompactConverter
     */
    private $compactConverter;
    /**
     * @readonly
     * @var \Rector\CodeQuality\NodeAnalyzer\ArrayItemsAnalyzer
     */
    private $arrayItemsAnalyzer;
    /**
     * @readonly
     * @var \Rector\CodeQuality\NodeAnalyzer\ArrayCompacter
     */
    private $arrayCompacter;
    public function __construct(\Rector\CodeQuality\CompactConverter $compactConverter, \Rector\CodeQuality\NodeAnalyzer\ArrayItemsAnalyzer $arrayItemsAnalyzer, \Rector\CodeQuality\NodeAnalyzer\ArrayCompacter $arrayCompacter)
    {
        $this->compactConverter = $compactConverter;
        $this->arrayItemsAnalyzer = $arrayItemsAnalyzer;
        $this->arrayCompacter = $arrayCompacter;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Change compact() call to own array', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $checkout = 'one';
        $form = 'two';

        return compact('checkout', 'form');
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $checkout = 'one';
        $form = 'two';

        return ['checkout' => $checkout, 'form' => $form];
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
        if (!$this->isName($node, 'compact')) {
            return null;
        }
        if ($this->compactConverter->hasAllArgumentsNamed($node)) {
            return $this->compactConverter->convertToArray($node);
        }
        /** @var Arg $firstArg */
        $firstArg = $node->args[0];
        $firstValue = $firstArg->value;
        $firstValueStaticType = $this->getType($firstValue);
        if (!$firstValueStaticType instanceof \PHPStan\Type\Constant\ConstantArrayType) {
            return null;
        }
        if ($firstValueStaticType->getItemType() instanceof \PHPStan\Type\MixedType) {
            return null;
        }
        return $this->refactorAssignArray($firstValue, $node);
    }
    private function refactorAssignedArray(\PhpParser\Node\Expr\Assign $assign, \PhpParser\Node\Expr\FuncCall $funcCall, \PhpParser\Node\Expr $expr) : ?\PhpParser\Node\Expr
    {
        if (!$assign->expr instanceof \PhpParser\Node\Expr\Array_) {
            return null;
        }
        $array = $assign->expr;
        $assignScope = $assign->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        if (!$assignScope instanceof \PHPStan\Analyser\Scope) {
            return null;
        }
        $currentStatement = $this->betterNodeFinder->resolveCurrentStatement($funcCall);
        if (!$currentStatement instanceof \PhpParser\Node\Stmt) {
            return null;
        }
        $isCompactOfUndefinedVariables = $this->arrayItemsAnalyzer->hasArrayExclusiveDefinedVariableNames($array, $assignScope);
        if ($isCompactOfUndefinedVariables) {
            $funcCallScope = $funcCall->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
            if (!$funcCallScope instanceof \PHPStan\Analyser\Scope) {
                return null;
            }
            $isCompactOfDefinedVariables = $this->arrayItemsAnalyzer->hasArrayExclusiveUndefinedVariableNames($array, $funcCallScope);
            if ($isCompactOfDefinedVariables) {
                $this->arrayCompacter->compactStringToVariableArray($array);
                return $expr;
            }
        }
        $this->removeNode($assign);
        $this->arrayCompacter->compactStringToVariableArray($array);
        /** @var Arg $firstArg */
        $firstArg = $funcCall->args[0];
        $assignVariable = $firstArg->value;
        $preAssign = new \PhpParser\Node\Expr\Assign($assignVariable, $array);
        $this->nodesToAddCollector->addNodeBeforeNode($preAssign, $currentStatement);
        return $expr;
    }
    private function refactorAssignArray(\PhpParser\Node\Expr $expr, \PhpParser\Node\Expr\FuncCall $funcCall) : ?\PhpParser\Node\Expr
    {
        $previousAssign = $this->betterNodeFinder->findPreviousAssignToExpr($expr);
        if (!$previousAssign instanceof \PhpParser\Node\Expr\Assign) {
            return null;
        }
        return $this->refactorAssignedArray($previousAssign, $funcCall, $expr);
    }
}
