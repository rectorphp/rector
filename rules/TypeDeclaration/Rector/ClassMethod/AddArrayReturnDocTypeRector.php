<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\ArrayShapeNode;
use PHPStan\PhpDocParser\Ast\Type\ArrayTypeNode;
use PHPStan\PhpDocParser\Ast\Type\GenericTypeNode;
use PHPStan\Type\ArrayType;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\IterableType;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use PHPStan\Type\VoidType;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Rector\Core\Rector\AbstractRector;
use Rector\DeadCode\PhpDoc\TagRemover\ReturnTagRemover;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Privatization\TypeManipulator\NormalizeTypeToRespectArrayScalarType;
use Rector\TypeDeclaration\NodeTypeAnalyzer\DetailedTypeAnalyzer;
use Rector\TypeDeclaration\TypeAnalyzer\AdvancedArrayAnalyzer;
use Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer;
use Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer\ReturnTypeDeclarationReturnTypeInferer;
use Rector\VendorLocker\NodeVendorLocker\ClassMethodReturnTypeOverrideGuard;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddArrayReturnDocTypeRector\AddArrayReturnDocTypeRectorTest
 */
final class AddArrayReturnDocTypeRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var \Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer
     */
    private $returnTypeInferer;
    /**
     * @var \Rector\VendorLocker\NodeVendorLocker\ClassMethodReturnTypeOverrideGuard
     */
    private $classMethodReturnTypeOverrideGuard;
    /**
     * @var \Rector\TypeDeclaration\TypeAnalyzer\AdvancedArrayAnalyzer
     */
    private $advancedArrayAnalyzer;
    /**
     * @var \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger
     */
    private $phpDocTypeChanger;
    /**
     * @var \Rector\Privatization\TypeManipulator\NormalizeTypeToRespectArrayScalarType
     */
    private $normalizeTypeToRespectArrayScalarType;
    /**
     * @var \Rector\DeadCode\PhpDoc\TagRemover\ReturnTagRemover
     */
    private $returnTagRemover;
    /**
     * @var \Rector\TypeDeclaration\NodeTypeAnalyzer\DetailedTypeAnalyzer
     */
    private $detailedTypeAnalyzer;
    public function __construct(\Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer $returnTypeInferer, \Rector\VendorLocker\NodeVendorLocker\ClassMethodReturnTypeOverrideGuard $classMethodReturnTypeOverrideGuard, \Rector\TypeDeclaration\TypeAnalyzer\AdvancedArrayAnalyzer $advancedArrayAnalyzer, \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger $phpDocTypeChanger, \Rector\Privatization\TypeManipulator\NormalizeTypeToRespectArrayScalarType $normalizeTypeToRespectArrayScalarType, \Rector\DeadCode\PhpDoc\TagRemover\ReturnTagRemover $returnTagRemover, \Rector\TypeDeclaration\NodeTypeAnalyzer\DetailedTypeAnalyzer $detailedTypeAnalyzer)
    {
        $this->returnTypeInferer = $returnTypeInferer;
        $this->classMethodReturnTypeOverrideGuard = $classMethodReturnTypeOverrideGuard;
        $this->advancedArrayAnalyzer = $advancedArrayAnalyzer;
        $this->phpDocTypeChanger = $phpDocTypeChanger;
        $this->normalizeTypeToRespectArrayScalarType = $normalizeTypeToRespectArrayScalarType;
        $this->returnTagRemover = $returnTagRemover;
        $this->detailedTypeAnalyzer = $detailedTypeAnalyzer;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Adds @return annotation to array parameters inferred from the rest of the code', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    /**
     * @var int[]
     */
    private $values;

    public function getValues(): array
    {
        return $this->values;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    /**
     * @var int[]
     */
    private $values;

    /**
     * @return int[]
     */
    public function getValues(): array
    {
        return $this->values;
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
        return [\PhpParser\Node\Stmt\ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        if ($this->shouldSkip($node, $phpDocInfo)) {
            return null;
        }
        $inferredReturnType = $this->returnTypeInferer->inferFunctionLikeWithExcludedInferers($node, [\Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer\ReturnTypeDeclarationReturnTypeInferer::class]);
        $inferredReturnType = $this->normalizeTypeToRespectArrayScalarType->normalizeToArray($inferredReturnType, $node->returnType);
        $currentReturnType = $phpDocInfo->getReturnType();
        if ($this->classMethodReturnTypeOverrideGuard->shouldSkipClassMethodOldTypeWithNewType($currentReturnType, $inferredReturnType)) {
            return null;
        }
        if ($this->shouldSkipType($inferredReturnType, $currentReturnType, $node, $phpDocInfo)) {
            return null;
        }
        $this->phpDocTypeChanger->changeReturnType($phpDocInfo, $inferredReturnType);
        if ($phpDocInfo->hasChanged()) {
            $node->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::HAS_PHP_DOC_INFO_JUST_CHANGED, \true);
            $this->returnTagRemover->removeReturnTagIfUseless($phpDocInfo, $node);
            return $node;
        }
        return null;
    }
    private function shouldSkip(\PhpParser\Node\Stmt\ClassMethod $classMethod, \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo $phpDocInfo) : bool
    {
        if ($this->shouldSkipClassMethod($classMethod)) {
            return \true;
        }
        if ($this->hasArrayShapeNode($classMethod)) {
            return \true;
        }
        $currentPhpDocReturnType = $phpDocInfo->getReturnType();
        if ($currentPhpDocReturnType instanceof \PHPStan\Type\ArrayType && $currentPhpDocReturnType->getItemType() instanceof \PHPStan\Type\MixedType) {
            return \true;
        }
        if ($this->hasInheritDoc($classMethod)) {
            return \true;
        }
        return $currentPhpDocReturnType instanceof \PHPStan\Type\IterableType;
    }
    /**
     * @deprecated
     * @todo merge to
     * @see \Rector\TypeDeclaration\TypeAlreadyAddedChecker\ReturnTypeAlreadyAddedChecker
     */
    private function shouldSkipType(\PHPStan\Type\Type $newType, \PHPStan\Type\Type $currentType, \PhpParser\Node\Stmt\ClassMethod $classMethod, \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo $phpDocInfo) : bool
    {
        if ($newType instanceof \PHPStan\Type\ArrayType && $this->shouldSkipArrayType($newType, $classMethod, $phpDocInfo)) {
            return \true;
        }
        // not an array type
        if ($newType instanceof \PHPStan\Type\VoidType) {
            return \true;
        }
        if ($this->advancedArrayAnalyzer->isMoreSpecificArrayTypeOverride($newType, $classMethod, $phpDocInfo)) {
            return \true;
        }
        if ($this->isGenericTypeToMixedTypeOverride($newType, $currentType)) {
            return \true;
        }
        return $this->detailedTypeAnalyzer->isTooDetailed($newType);
    }
    private function shouldSkipClassMethod(\PhpParser\Node\Stmt\ClassMethod $classMethod) : bool
    {
        if ($this->classMethodReturnTypeOverrideGuard->shouldSkipClassMethod($classMethod)) {
            return \true;
        }
        if ($classMethod->returnType === null) {
            return \false;
        }
        return !$this->isNames($classMethod->returnType, ['array', 'iterable', 'Iterator']);
    }
    private function hasArrayShapeNode(\PhpParser\Node\Stmt\ClassMethod $classMethod) : bool
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);
        $returnTagValueNode = $phpDocInfo->getReturnTagValue();
        if (!$returnTagValueNode instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode) {
            return \false;
        }
        if ($returnTagValueNode->type instanceof \PHPStan\PhpDocParser\Ast\Type\GenericTypeNode) {
            return \true;
        }
        if ($returnTagValueNode->type instanceof \PHPStan\PhpDocParser\Ast\Type\ArrayShapeNode) {
            return \true;
        }
        if (!$returnTagValueNode->type instanceof \PHPStan\PhpDocParser\Ast\Type\ArrayTypeNode) {
            return \false;
        }
        return $returnTagValueNode->type->type instanceof \PHPStan\PhpDocParser\Ast\Type\ArrayShapeNode;
    }
    private function hasInheritDoc(\PhpParser\Node\Stmt\ClassMethod $classMethod) : bool
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);
        return $phpDocInfo->hasInheritDoc();
    }
    private function shouldSkipArrayType(\PHPStan\Type\ArrayType $arrayType, \PhpParser\Node\Stmt\ClassMethod $classMethod, \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo $phpDocInfo) : bool
    {
        if ($this->advancedArrayAnalyzer->isNewAndCurrentTypeBothCallable($arrayType, $phpDocInfo)) {
            return \true;
        }
        if ($this->advancedArrayAnalyzer->isClassStringArrayByStringArrayOverride($arrayType, $classMethod)) {
            return \true;
        }
        return $this->advancedArrayAnalyzer->isMixedOfSpecificOverride($arrayType, $phpDocInfo);
    }
    private function isGenericTypeToMixedTypeOverride(\PHPStan\Type\Type $newType, \PHPStan\Type\Type $currentType) : bool
    {
        if ($newType instanceof \PHPStan\Type\Generic\GenericObjectType && $currentType instanceof \PHPStan\Type\MixedType) {
            $types = $newType->getTypes();
            if ($types[0] instanceof \PHPStan\Type\MixedType && $types[1] instanceof \PHPStan\Type\ArrayType) {
                return \true;
            }
        }
        return \false;
    }
}
