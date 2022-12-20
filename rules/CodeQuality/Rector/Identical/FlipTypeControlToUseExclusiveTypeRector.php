<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\Identical;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\Empty_;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Expression;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
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
        return [Identical::class, Empty_::class];
    }
    /**
     * @param Identical|Empty_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($node instanceof Identical) {
            return $this->refactorIdentical($node);
        }
        $exprType = $this->getType($node->expr);
        if (!$exprType instanceof ObjectType) {
            return null;
        }
        // the empty false positively reports epxr, even if nullable
        $assign = $this->betterNodeFinder->findPreviousAssignToExpr($node->expr);
        if (!$assign instanceof Expr) {
            return null;
        }
        $previousAssignToExprType = $this->getType($assign);
        $types = $this->getExactlyTwoUnionedTypes($previousAssignToExprType);
        if ($this->isNotNullOneOf($types)) {
            return null;
        }
        return $this->processConvertToExclusiveType($types, $node->expr);
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
    /**
     * @return Type[]
     */
    private function getExactlyTwoUnionedTypes(Type $type) : array
    {
        if (!$type instanceof UnionType) {
            return [];
        }
        $types = $type->getTypes();
        if (\count($types) > 2) {
            return [];
        }
        return $types;
    }
    /**
     * @param Type[] $types
     */
    private function isNotNullOneOf(array $types) : bool
    {
        if ($types === []) {
            return \true;
        }
        if ($types[0] === $types[1]) {
            return \true;
        }
        if ($types[0] instanceof NullType) {
            return \false;
        }
        return !$types[1] instanceof NullType;
    }
    /**
     * @param Type[] $types
     */
    private function processConvertToExclusiveType(array $types, Expr $expr, ?PhpDocInfo $phpDocInfo = null) : ?BooleanNot
    {
        $type = $types[0] instanceof NullType ? $types[1] : $types[0];
        if (!$type instanceof FullyQualifiedObjectType && !$type instanceof ObjectType) {
            return null;
        }
        if ($phpDocInfo instanceof PhpDocInfo) {
            $varTagValueNode = $phpDocInfo->getVarTagValueNode();
            if ($varTagValueNode instanceof VarTagValueNode) {
                $this->phpDocTagRemover->removeTagValueFromNode($phpDocInfo, $varTagValueNode);
            }
        }
        $fullyQualifiedType = $type instanceof ShortenedObjectType ? $type->getFullyQualifiedName() : $type->getClassName();
        return new BooleanNot(new Instanceof_($expr, new FullyQualified($fullyQualifiedType)));
    }
    private function refactorIdentical(Identical $identical) : ?BooleanNot
    {
        $expr = $this->matchNullComparedExpr($identical);
        if (!$expr instanceof Expr) {
            return null;
        }
        $node = $this->getVariableAssign($identical, $expr);
        if (!$node instanceof Assign) {
            return null;
        }
        $expression = $node->getAttribute(AttributeKey::PARENT_NODE);
        if (!$expression instanceof Expression) {
            return null;
        }
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($expression);
        $type = $phpDocInfo->getVarType();
        if (!$type instanceof UnionType) {
            $type = $this->getType($node->expr);
        }
        if (!$type instanceof UnionType) {
            return null;
        }
        /** @var Type[] $types */
        $types = $this->getExactlyTwoUnionedTypes($type);
        if ($this->isNotNullOneOf($types)) {
            return null;
        }
        return $this->processConvertToExclusiveType($types, $expr, $phpDocInfo);
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
