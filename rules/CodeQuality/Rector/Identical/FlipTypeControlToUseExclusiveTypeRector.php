<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\Identical;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PHPStan\Type\ObjectType;
use Rector\PhpParser\Node\NullableObjectToInstanceofFactory;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\Rector\AbstractRector;
use Rector\RuleDoc\CodeSample\CodeSample;
use Rector\RuleDoc\RuleDefinition;
use Rector\TypeDeclaration\TypeAnalyzer\NullableTypeAnalyzer;
/**
 * @see \Rector\Tests\CodeQuality\Rector\Identical\FlipTypeControlToUseExclusiveTypeRector\FlipTypeControlToUseExclusiveTypeRectorTest
 */
final class FlipTypeControlToUseExclusiveTypeRector extends AbstractRector
{
    /**
     * @readonly
     */
    private NullableTypeAnalyzer $nullableTypeAnalyzer;
    /**
     * @readonly
     */
    private ValueResolver $valueResolver;
    /**
     * @readonly
     */
    private NullableObjectToInstanceofFactory $nullableObjectToInstanceofFactory;
    public function __construct(NullableTypeAnalyzer $nullableTypeAnalyzer, ValueResolver $valueResolver, NullableObjectToInstanceofFactory $nullableObjectToInstanceofFactory)
    {
        $this->nullableTypeAnalyzer = $nullableTypeAnalyzer;
        $this->valueResolver = $valueResolver;
        $this->nullableObjectToInstanceofFactory = $nullableObjectToInstanceofFactory;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Flip type control from null compare to use exclusive instanceof object', [new CodeSample(<<<'CODE_SAMPLE'
function process(?DateTime $dateTime)
{
    if ($dateTime === null) {
        return;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
function process(?DateTime $dateTime)
{
    if (! $dateTime instanceof DateTime) {
        return;
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
        return [Identical::class, NotIdentical::class];
    }
    /**
     * @param Identical|NotIdentical $node
     */
    public function refactor(Node $node): ?Node
    {
        $expr = $this->matchNullComparedExpr($node);
        if (!$expr instanceof Expr) {
            return null;
        }
        $nullableObjectType = $this->nullableTypeAnalyzer->resolveNullableObjectType($expr);
        if (!$nullableObjectType instanceof ObjectType) {
            return null;
        }
        return $this->nullableObjectToInstanceofFactory->create($expr, $nullableObjectType, $node instanceof Identical);
    }
    /**
     * @param \PhpParser\Node\Expr\BinaryOp\Identical|\PhpParser\Node\Expr\BinaryOp\NotIdentical $binaryOp
     */
    private function matchNullComparedExpr($binaryOp): ?Expr
    {
        if ($this->valueResolver->isNull($binaryOp->left)) {
            return $binaryOp->right;
        }
        if ($this->valueResolver->isNull($binaryOp->right)) {
            return $binaryOp->left;
        }
        return null;
    }
}
