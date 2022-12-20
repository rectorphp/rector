<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\Identical;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Expression;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\UnionType;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
use Rector\StaticTypeMapper\ValueObject\Type\ShortenedObjectType;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\Identical\FlipTypeControlToUseExclusiveTypeRector\FlipTypeControlToUseExclusiveTypeRectorTest
 */
final class FlipTypeControlToUseExclusiveTypeRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover
     */
    private $phpDocTagRemover;
    public function __construct(PhpDocTagRemover $phpDocTagRemover)
    {
        $this->phpDocTagRemover = $phpDocTagRemover;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Flip type control to use exclusive type', [new CodeSample(<<<'CODE_SAMPLE'
/** @var PhpDocInfo|null $phpDocInfo */
$phpDocInfo = $functionLike->getAttribute(AttributeKey::PHP_DOC_INFO);
if ($phpDocInfo === null) {
    return;
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$phpDocInfo = $functionLike->getAttribute(AttributeKey::PHP_DOC_INFO);
if (! $phpDocInfo instanceof PhpDocInfo) {
    return;
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Identical::class];
    }
    /**
     * @param Identical $node
     */
    public function refactor(Node $node) : ?Node
    {
        $expr = $this->matchNullComparedExpr($node);
        if (!$expr instanceof Expr) {
            return null;
        }
        $assign = $this->getVariableAssign($node, $expr);
        if (!$assign instanceof Assign) {
            return null;
        }
        $bareType = $this->matchBareNullableType($expr);
        if (!$bareType instanceof Type) {
            return null;
        }
        $expression = $assign->getAttribute(AttributeKey::PARENT_NODE);
        if (!$expression instanceof Expression) {
            return null;
        }
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($expression);
        return $this->processConvertToExclusiveType($bareType, $expr, $phpDocInfo);
    }
    private function getVariableAssign(Identical $identical, Expr $expr) : ?Node
    {
        return $this->betterNodeFinder->findFirstPrevious($identical, function (Node $node) use($expr) : bool {
            if (!$node instanceof Assign) {
                return \false;
            }
            return $this->nodeComparator->areNodesEqual($node->var, $expr);
        });
    }
    private function matchBareNullableType(Expr $expr) : ?Type
    {
        $exprType = $this->getType($expr);
        if (!$exprType instanceof UnionType) {
            return null;
        }
        if (!TypeCombinator::containsNull($exprType)) {
            return null;
        }
        if (\count($exprType->getTypes()) !== 2) {
            return null;
        }
        return TypeCombinator::removeNull($exprType);
    }
    private function processConvertToExclusiveType(Type $type, Expr $expr, PhpDocInfo $phpDocInfo) : ?BooleanNot
    {
        // $type = $types[0] instanceof NullType ? $types[1] : $types[0];
        if (!$type instanceof FullyQualifiedObjectType && !$type instanceof ObjectType) {
            return null;
        }
        $varTagValueNode = $phpDocInfo->getVarTagValueNode();
        if ($varTagValueNode instanceof VarTagValueNode) {
            $this->phpDocTagRemover->removeTagValueFromNode($phpDocInfo, $varTagValueNode);
        }
        $fullyQualifiedType = $type instanceof ShortenedObjectType ? $type->getFullyQualifiedName() : $type->getClassName();
        return new BooleanNot(new Instanceof_($expr, new FullyQualified($fullyQualifiedType)));
    }
    private function matchNullComparedExpr(Identical $identical) : ?Expr
    {
        if ($this->valueResolver->isNull($identical->left)) {
            return $identical->right;
        }
        if ($this->valueResolver->isNull($identical->right)) {
            return $identical->left;
        }
        return null;
    }
}
