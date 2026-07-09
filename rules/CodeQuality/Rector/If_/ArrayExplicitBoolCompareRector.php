<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\If_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\Cast\Bool_;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\ElseIf_;
use PhpParser\Node\Stmt\If_;
use PHPStan\Type\MixedType;
use Rector\NodeTypeResolver\TypeAnalyzer\ArrayTypeAnalyzer;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\If_\ArrayExplicitBoolCompareRector\ArrayExplicitBoolCompareRectorTest
 */
final class ArrayExplicitBoolCompareRector extends AbstractRector
{
    /**
     * @readonly
     */
    private ArrayTypeAnalyzer $arrayTypeAnalyzer;
    public function __construct(ArrayTypeAnalyzer $arrayTypeAnalyzer)
    {
        $this->arrayTypeAnalyzer = $arrayTypeAnalyzer;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Make array if conditions more explicit', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeController
{
    public function run(array $items)
    {
        if (!$items) {
            return 'no items';
        }
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeController
{
    public function run(array $items)
    {
        if ($items === []) {
            return 'no items';
        }
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [If_::class, ElseIf_::class, Ternary::class];
    }
    /**
     * @param If_|ElseIf_|Ternary $node
     */
    public function refactor(Node $node): ?Node
    {
        // skip short ternary
        if ($node instanceof Ternary && !$node->if instanceof Expr) {
            return null;
        }
        if ($node->cond instanceof BooleanNot) {
            $conditionNode = $node->cond->expr;
            $isNegated = \true;
        } else {
            $conditionNode = $node->cond;
            $isNegated = \false;
        }
        if ($conditionNode instanceof Bool_) {
            return null;
        }
        $conditionStaticType = $this->nodeTypeResolver->getNativeType($conditionNode);
        if ($conditionStaticType instanceof MixedType || $conditionStaticType->isBoolean()->yes()) {
            return null;
        }
        if (!$this->arrayTypeAnalyzer->isArrayType($conditionNode)) {
            return null;
        }
        $binaryOp = $this->resolveArray($isNegated, $conditionNode);
        if (!$binaryOp instanceof Expr) {
            return null;
        }
        $node->cond = $binaryOp;
        return $node;
    }
    /**
     * @return \PhpParser\Node\Expr\BinaryOp\Identical|\PhpParser\Node\Expr\BinaryOp\NotIdentical|null
     */
    private function resolveArray(bool $isNegated, Expr $expr)
    {
        if (!$expr instanceof Variable) {
            return null;
        }
        $array = new Array_([]);
        // compare === []
        if ($isNegated) {
            return new Identical($expr, $array);
        }
        return new NotIdentical($expr, $array);
    }
}
