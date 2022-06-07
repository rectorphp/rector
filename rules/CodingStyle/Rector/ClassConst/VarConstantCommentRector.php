<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Rector\ClassConst;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassConst;
use PHPStan\PhpDocParser\Ast\Type\ArrayTypeNode;
use PHPStan\PhpDocParser\Ast\Type\GenericTypeNode;
use PHPStan\PhpDocParser\Ast\Type\UnionTypeNode;
use PHPStan\Type\ArrayType;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\TypeComparator\TypeComparator;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\Privatization\TypeManipulator\TypeNormalizer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodingStyle\Rector\ClassConst\VarConstantCommentRector\VarConstantCommentRectorTest
 */
final class VarConstantCommentRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\TypeComparator\TypeComparator
     */
    private $typeComparator;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger
     */
    private $phpDocTypeChanger;
    /**
     * @readonly
     * @var \Rector\Privatization\TypeManipulator\TypeNormalizer
     */
    private $typeNormalizer;
    public function __construct(TypeComparator $typeComparator, PhpDocTypeChanger $phpDocTypeChanger, TypeNormalizer $typeNormalizer)
    {
        $this->typeComparator = $typeComparator;
        $this->phpDocTypeChanger = $phpDocTypeChanger;
        $this->typeNormalizer = $typeNormalizer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Constant should have a @var comment with type', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    const HI = 'hi';
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    /**
     * @var string
     */
    const HI = 'hi';
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [ClassConst::class];
    }
    /**
     * @param ClassConst $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (\count($node->consts) > 1) {
            return null;
        }
        $constType = $this->getType($node->consts[0]->value);
        if ($constType instanceof MixedType) {
            return null;
        }
        // generalize false/true type to bool, as mostly default value but accepts both
        $constType = $this->typeNormalizer->generalizeConstantBoolTypes($constType);
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        if ($this->shouldSkipConstantArrayType($constType, $phpDocInfo)) {
            return null;
        }
        if ($this->typeComparator->isSubtype($constType, $phpDocInfo->getVarType())) {
            return null;
        }
        $this->phpDocTypeChanger->changeVarType($phpDocInfo, $constType);
        if (!$phpDocInfo->hasChanged()) {
            return null;
        }
        return $node;
    }
    private function hasTwoAndMoreGenericClassStringTypes(ConstantArrayType $constantArrayType) : bool
    {
        $typeNode = $this->staticTypeMapper->mapPHPStanTypeToPHPStanPhpDocTypeNode($constantArrayType, TypeKind::RETURN);
        if (!$typeNode instanceof ArrayTypeNode) {
            return \false;
        }
        if (!$typeNode->type instanceof UnionTypeNode) {
            return \false;
        }
        $genericTypeNodeCount = 0;
        foreach ($typeNode->type->types as $unionedTypeNode) {
            if ($unionedTypeNode instanceof GenericTypeNode) {
                ++$genericTypeNodeCount;
            }
        }
        return $genericTypeNodeCount > 1;
    }
    /**
     * Skip big arrays and mixed[] constants
     */
    private function shouldSkipConstantArrayType(Type $constType, PhpDocInfo $phpDocInfo) : bool
    {
        if (!$constType instanceof ConstantArrayType) {
            return \false;
        }
        $currentVarType = $phpDocInfo->getVarType();
        if ($currentVarType instanceof ArrayType && $currentVarType->getItemType() instanceof MixedType) {
            return \true;
        }
        if ($this->hasTwoAndMoreGenericClassStringTypes($constType)) {
            return \true;
        }
        return $this->isHugeNestedConstantArrayTyp($constType);
    }
    private function isHugeNestedConstantArrayTyp(ConstantArrayType $constantArrayType) : bool
    {
        if (\count($constantArrayType->getValueTypes()) <= 3) {
            return \false;
        }
        foreach ($constantArrayType->getValueTypes() as $constValueType) {
            if ($constValueType instanceof ConstantArrayType) {
                return \true;
            }
        }
        return \false;
    }
}
