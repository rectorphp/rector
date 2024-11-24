<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\Identical;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Name\FullyQualified;
use PHPStan\Type\ObjectType;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\Rector\AbstractRector;
use Rector\StaticTypeMapper\ValueObject\Type\AliasedObjectType;
use Rector\StaticTypeMapper\ValueObject\Type\ShortenedObjectType;
use Rector\TypeDeclaration\TypeAnalyzer\NullableTypeAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
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
    public function __construct(NullableTypeAnalyzer $nullableTypeAnalyzer, ValueResolver $valueResolver)
    {
        $this->nullableTypeAnalyzer = $nullableTypeAnalyzer;
        $this->valueResolver = $valueResolver;
    }
    public function getRuleDefinition() : RuleDefinition
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
    public function getNodeTypes() : array
    {
        return [Identical::class, NotIdentical::class];
    }
    /**
     * @param Identical|NotIdentical $node
     */
    public function refactor(Node $node) : ?Node
    {
        $expr = $this->matchNullComparedExpr($node);
        if (!$expr instanceof Expr) {
            return null;
        }
        $nullableObjectType = $this->nullableTypeAnalyzer->resolveNullableObjectType($expr);
        if (!$nullableObjectType instanceof ObjectType) {
            return null;
        }
        return $this->processConvertToExclusiveType($nullableObjectType, $expr, $node);
    }
    /**
     * @param \PhpParser\Node\Expr\BinaryOp\Identical|\PhpParser\Node\Expr\BinaryOp\NotIdentical $binaryOp
     * @return \PhpParser\Node\Expr\BooleanNot|\PhpParser\Node\Expr\Instanceof_
     */
    private function processConvertToExclusiveType(ObjectType $objectType, Expr $expr, $binaryOp)
    {
        $fullyQualifiedType = $objectType instanceof ShortenedObjectType || $objectType instanceof AliasedObjectType ? $objectType->getFullyQualifiedName() : $objectType->getClassName();
        $instanceof = new Instanceof_($expr, new FullyQualified($fullyQualifiedType));
        if ($binaryOp instanceof NotIdentical) {
            return $instanceof;
        }
        return new BooleanNot($instanceof);
    }
    /**
     * @param \PhpParser\Node\Expr\BinaryOp\Identical|\PhpParser\Node\Expr\BinaryOp\NotIdentical $binaryOp
     */
    private function matchNullComparedExpr($binaryOp) : ?Expr
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
