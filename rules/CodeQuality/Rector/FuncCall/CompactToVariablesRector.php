<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\CodeQuality\Rector\FuncCall;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Arg;
use RectorPrefix20220606\PhpParser\Node\Expr;
use RectorPrefix20220606\PhpParser\Node\Expr\Array_;
use RectorPrefix20220606\PhpParser\Node\Expr\Assign;
use RectorPrefix20220606\PhpParser\Node\Expr\FuncCall;
use RectorPrefix20220606\PhpParser\Node\Stmt;
use RectorPrefix20220606\PHPStan\Analyser\Scope;
use RectorPrefix20220606\PHPStan\Type\Constant\ConstantArrayType;
use RectorPrefix20220606\PHPStan\Type\MixedType;
use RectorPrefix20220606\Rector\CodeQuality\CompactConverter;
use RectorPrefix20220606\Rector\CodeQuality\NodeAnalyzer\ArrayCompacter;
use RectorPrefix20220606\Rector\CodeQuality\NodeAnalyzer\ArrayItemsAnalyzer;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Rector\NodeTypeResolver\Node\AttributeKey;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://stackoverflow.com/a/16319909/1348344
 * @changelog https://3v4l.org/8GJEs
 * @see \Rector\Tests\CodeQuality\Rector\FuncCall\CompactToVariablesRector\CompactToVariablesRectorTest
 */
final class CompactToVariablesRector extends AbstractRector
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
    public function __construct(CompactConverter $compactConverter, ArrayItemsAnalyzer $arrayItemsAnalyzer, ArrayCompacter $arrayCompacter)
    {
        $this->compactConverter = $compactConverter;
        $this->arrayItemsAnalyzer = $arrayItemsAnalyzer;
        $this->arrayCompacter = $arrayCompacter;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change compact() call to own array', [new CodeSample(<<<'CODE_SAMPLE'
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
        return [FuncCall::class];
    }
    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node) : ?Node
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
        if (!$firstValueStaticType instanceof ConstantArrayType) {
            return null;
        }
        if ($firstValueStaticType->getItemType() instanceof MixedType) {
            return null;
        }
        return $this->refactorAssignArray($firstValue, $node);
    }
    private function refactorAssignedArray(Assign $assign, FuncCall $funcCall, Expr $expr) : ?Expr
    {
        if (!$assign->expr instanceof Array_) {
            return null;
        }
        $array = $assign->expr;
        $assignScope = $assign->getAttribute(AttributeKey::SCOPE);
        if (!$assignScope instanceof Scope) {
            return null;
        }
        $currentStmt = $this->betterNodeFinder->resolveCurrentStatement($funcCall);
        if (!$currentStmt instanceof Stmt) {
            return null;
        }
        $isCompactOfUndefinedVariables = $this->arrayItemsAnalyzer->hasArrayExclusiveDefinedVariableNames($array, $assignScope);
        if ($isCompactOfUndefinedVariables) {
            $funcCallScope = $funcCall->getAttribute(AttributeKey::SCOPE);
            if (!$funcCallScope instanceof Scope) {
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
        $preAssign = new Assign($assignVariable, $array);
        $this->nodesToAddCollector->addNodeBeforeNode($preAssign, $currentStmt, $this->file->getSmartFileInfo());
        return $expr;
    }
    private function refactorAssignArray(Expr $expr, FuncCall $funcCall) : ?Expr
    {
        $previousAssign = $this->betterNodeFinder->findPreviousAssignToExpr($expr);
        if (!$previousAssign instanceof Assign) {
            return null;
        }
        return $this->refactorAssignedArray($previousAssign, $funcCall, $expr);
    }
}
