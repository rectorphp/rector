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
final class FlipTypeControlToUseExclusiveTypeRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover
     */
    private $phpDocTagRemover;
    public function __construct(\Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover $phpDocTagRemover)
    {
        $this->phpDocTagRemover = $phpDocTagRemover;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Flip type control to use exclusive type', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function __construct(array $values)
    {
        /** @var PhpDocInfo|null $phpDocInfo */
        $phpDocInfo = $functionLike->getAttribute(AttributeKey::PHP_DOC_INFO);
        if ($phpDocInfo === null) {
            return;
        }
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function __construct(array $values)
    {
        $phpDocInfo = $functionLike->getAttribute(AttributeKey::PHP_DOC_INFO);
        if (! $phpDocInfo instanceof PhpDocInfo) {
            return;
        }
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
        return [\PhpParser\Node\Expr\BinaryOp\Identical::class];
    }
    /**
     * @param Identical $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$this->valueResolver->isNull($node->left) && !$this->valueResolver->isNull($node->right)) {
            return null;
        }
        $variable = $this->valueResolver->isNull($node->left) ? $node->right : $node->left;
        $assign = $this->getVariableAssign($node, $variable);
        if (!$assign instanceof \PhpParser\Node\Expr\Assign) {
            return null;
        }
        $expression = $assign->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if (!$expression instanceof \PhpParser\Node\Stmt\Expression) {
            return null;
        }
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($expression);
        $type = $phpDocInfo->getVarType();
        if (!$type instanceof \PHPStan\Type\UnionType) {
            $type = $this->getType($assign->expr);
        }
        if (!$type instanceof \PHPStan\Type\UnionType) {
            return null;
        }
        /** @var Type[] $types */
        $types = $this->getTypes($type);
        if ($this->isNotNullOneOf($types)) {
            return null;
        }
        return $this->processConvertToExclusiveType($types, $variable, $phpDocInfo);
    }
    private function getVariableAssign(\PhpParser\Node\Expr\BinaryOp\Identical $identical, \PhpParser\Node\Expr $expr) : ?\PhpParser\Node
    {
        return $this->betterNodeFinder->findFirstPrevious($identical, function (\PhpParser\Node $node) use($expr) : bool {
            if (!$node instanceof \PhpParser\Node\Expr\Assign) {
                return \false;
            }
            return $this->nodeComparator->areNodesEqual($node->var, $expr);
        });
    }
    /**
     * @return Type[]
     */
    private function getTypes(\PHPStan\Type\UnionType $unionType) : array
    {
        $types = $unionType->getTypes();
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
        if ($types[0] instanceof \PHPStan\Type\NullType) {
            return \false;
        }
        return !$types[1] instanceof \PHPStan\Type\NullType;
    }
    /**
     * @param Type[] $types
     */
    private function processConvertToExclusiveType(array $types, \PhpParser\Node\Expr $expr, \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo $phpDocInfo) : ?\PhpParser\Node\Expr\BooleanNot
    {
        $type = $types[0] instanceof \PHPStan\Type\NullType ? $types[1] : $types[0];
        if (!$type instanceof \Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType && !$type instanceof \PHPStan\Type\ObjectType) {
            return null;
        }
        $varTagValueNode = $phpDocInfo->getVarTagValueNode();
        if ($varTagValueNode instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode) {
            $this->phpDocTagRemover->removeTagValueFromNode($phpDocInfo, $varTagValueNode);
        }
        $fullyQualifiedType = $type instanceof \Rector\StaticTypeMapper\ValueObject\Type\ShortenedObjectType ? $type->getFullyQualifiedName() : $type->getClassName();
        return new \PhpParser\Node\Expr\BooleanNot(new \PhpParser\Node\Expr\Instanceof_($expr, new \PhpParser\Node\Name\FullyQualified($fullyQualifiedType)));
    }
}
