<?php

declare(strict_types=1);

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
final class AddArrayReturnDocTypeRector extends AbstractRector
{
    public function __construct(
        private ReturnTypeInferer $returnTypeInferer,
        private ClassMethodReturnTypeOverrideGuard $classMethodReturnTypeOverrideGuard,
        private AdvancedArrayAnalyzer $advancedArrayAnalyzer,
        private PhpDocTypeChanger $phpDocTypeChanger,
        private NormalizeTypeToRespectArrayScalarType $normalizeTypeToRespectArrayScalarType,
        private ReturnTagRemover $returnTagRemover,
        private DetailedTypeAnalyzer $detailedTypeAnalyzer
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Adds @return annotation to array parameters inferred from the rest of the code',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
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
                    ,
                    <<<'CODE_SAMPLE'
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
                ),
            ]
        );
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class];
    }

    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        if ($this->shouldSkip($node, $phpDocInfo)) {
            return null;
        }

        $inferredReturnType = $this->returnTypeInferer->inferFunctionLikeWithExcludedInferers(
            $node,
            [ReturnTypeDeclarationReturnTypeInferer::class]
        );

        $inferredReturnType = $this->normalizeTypeToRespectArrayScalarType->normalizeToArray(
            $inferredReturnType,
            $node->returnType
        );

        $currentReturnType = $phpDocInfo->getReturnType();

        if ($this->classMethodReturnTypeOverrideGuard->shouldSkipClassMethodOldTypeWithNewType(
            $currentReturnType,
            $inferredReturnType
        )) {
            return null;
        }

        if ($this->shouldSkipType($inferredReturnType, $currentReturnType, $node, $phpDocInfo)) {
            return null;
        }

        $this->phpDocTypeChanger->changeReturnType($phpDocInfo, $inferredReturnType);

        if ($phpDocInfo->hasChanged()) {
            $node->setAttribute(AttributeKey::HAS_PHP_DOC_INFO_JUST_CHANGED, true);
            $this->returnTagRemover->removeReturnTagIfUseless($phpDocInfo, $node);
            return $node;
        }

        return null;
    }

    private function shouldSkip(ClassMethod $classMethod, PhpDocInfo $phpDocInfo): bool
    {
        if ($this->shouldSkipClassMethod($classMethod)) {
            return true;
        }

        if ($this->hasArrayShapeNode($classMethod)) {
            return true;
        }

        $currentPhpDocReturnType = $phpDocInfo->getReturnType();
        if ($currentPhpDocReturnType instanceof ArrayType && $currentPhpDocReturnType->getItemType() instanceof MixedType) {
            return true;
        }

        if ($this->hasInheritDoc($classMethod)) {
            return true;
        }

        return $currentPhpDocReturnType instanceof IterableType;
    }

    /**
     * @deprecated
     * @todo merge to
     * @see \Rector\TypeDeclaration\TypeAlreadyAddedChecker\ReturnTypeAlreadyAddedChecker
     */
    private function shouldSkipType(
        Type $newType,
        Type $currentType,
        ClassMethod $classMethod,
        PhpDocInfo $phpDocInfo
    ): bool {
        if ($newType instanceof ArrayType && $this->shouldSkipArrayType($newType, $classMethod, $phpDocInfo)) {
            return true;
        }

        if ($this->detailedTypeAnalyzer->isTooDetailed($newType)) {
            return true;
        }

        // not an array type
        if ($newType instanceof VoidType) {
            return true;
        }

        if ($this->advancedArrayAnalyzer->isMoreSpecificArrayTypeOverride($newType, $classMethod, $phpDocInfo)) {
            return true;
        }

        if ($this->isGenericTypeToMixedTypeOverride($newType, $currentType)) {
            return true;
        }

        return $this->detailedTypeAnalyzer->isTooDetailed($newType);
    }

    private function shouldSkipClassMethod(ClassMethod $classMethod): bool
    {
        if ($this->classMethodReturnTypeOverrideGuard->shouldSkipClassMethod($classMethod)) {
            return true;
        }

        if ($classMethod->returnType === null) {
            return false;
        }

        return ! $this->isNames($classMethod->returnType, ['array', 'iterable', 'Iterator']);
    }

    private function hasArrayShapeNode(ClassMethod $classMethod): bool
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);

        $returnTagValueNode = $phpDocInfo->getReturnTagValue();
        if (! $returnTagValueNode instanceof ReturnTagValueNode) {
            return false;
        }

        if ($returnTagValueNode->type instanceof GenericTypeNode) {
            return true;
        }

        if ($returnTagValueNode->type instanceof ArrayShapeNode) {
            return true;
        }

        if (! $returnTagValueNode->type instanceof ArrayTypeNode) {
            return false;
        }

        return $returnTagValueNode->type->type instanceof ArrayShapeNode;
    }

    private function hasInheritDoc(ClassMethod $classMethod): bool
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);
        return $phpDocInfo->hasInheritDoc();
    }

    private function shouldSkipArrayType(ArrayType $arrayType, ClassMethod $classMethod, PhpDocInfo $phpDocInfo): bool
    {
        if ($this->advancedArrayAnalyzer->isNewAndCurrentTypeBothCallable($arrayType, $phpDocInfo)) {
            return true;
        }

        if ($this->advancedArrayAnalyzer->isClassStringArrayByStringArrayOverride($arrayType, $classMethod)) {
            return true;
        }

        return $this->advancedArrayAnalyzer->isMixedOfSpecificOverride($arrayType, $phpDocInfo);
    }

    private function isGenericTypeToMixedTypeOverride(Type $newType, Type $currentType): bool
    {
        if ($newType instanceof GenericObjectType && $currentType instanceof MixedType) {
            $types = $newType->getTypes();
            if ($types[0] instanceof MixedType && $types[1] instanceof ArrayType) {
                return true;
            }
        }

        return false;
    }
}
