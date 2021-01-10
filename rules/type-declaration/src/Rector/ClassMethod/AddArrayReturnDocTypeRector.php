<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\ArrayShapeNode;
use PHPStan\PhpDocParser\Ast\Type\ArrayTypeNode;
use PHPStan\Type\ArrayType;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\IterableType;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use PHPStan\Type\VoidType;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpdocParserPrinter\ValueObject\TypeNode\AttributeAwareArrayShapeNode;
use Rector\PhpdocParserPrinter\ValueObject\TypeNode\AttributeAwareGenericTypeNode;
use Rector\TypeDeclaration\OverrideGuard\ClassMethodReturnTypeOverrideGuard;
use Rector\TypeDeclaration\TypeAnalyzer\AdvancedArrayAnalyzer;
use Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer;
use Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer\ReturnTypeDeclarationReturnTypeInferer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @sponsor Thanks https://spaceflow.io/ for sponsoring this rule - visit them on https://github.com/SpaceFlow-app
 *
 * @see \Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayReturnDocTypeRector\AddArrayReturnDocTypeRectorTest
 */
final class AddArrayReturnDocTypeRector extends AbstractRector
{
    /**
     * @var int
     */
    private const MAX_NUMBER_OF_TYPES = 3;

    /**
     * @var ReturnTypeInferer
     */
    private $returnTypeInferer;

    /**
     * @var ClassMethodReturnTypeOverrideGuard
     */
    private $classMethodReturnTypeOverrideGuard;

    /**
     * @var AdvancedArrayAnalyzer
     */
    private $advancedArrayAnalyzer;

    public function __construct(
        ReturnTypeInferer $returnTypeInferer,
        ClassMethodReturnTypeOverrideGuard $classMethodReturnTypeOverrideGuard,
        AdvancedArrayAnalyzer $advancedArrayAnalyzer
    ) {
        $this->returnTypeInferer = $returnTypeInferer;
        $this->classMethodReturnTypeOverrideGuard = $classMethodReturnTypeOverrideGuard;
        $this->advancedArrayAnalyzer = $advancedArrayAnalyzer;
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

            ]);
    }

    /**
     * @return string[]
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
        if ($this->shouldSkip($node)) {
            return null;
        }

        $inferedType = $this->returnTypeInferer->inferFunctionLikeWithExcludedInferers(
            $node,
            [ReturnTypeDeclarationReturnTypeInferer::class]
        );

        $currentReturnType = $this->getNodeReturnPhpDocType($node);
        if ($currentReturnType !== null && $this->classMethodReturnTypeOverrideGuard->shouldSkipClassMethodOldTypeWithNewType(
            $currentReturnType,
            $inferedType
        )) {
            return null;
        }

        if ($this->shouldSkipType($inferedType, $node)) {
            return null;
        }

        /** @var PhpDocInfo|null $phpDocInfo */
        $phpDocInfo = $node->getAttribute(AttributeKey::PHP_DOC_INFO);
        if ($phpDocInfo === null) {
            return null;
        }

        $phpDocInfo->changeReturnType($inferedType);

        return $node;
    }

    private function shouldSkip(ClassMethod $classMethod): bool
    {
        if ($this->shouldSkipClassMethod($classMethod)) {
            return true;
        }

        if ($this->hasArrayShapeNode($classMethod)) {
            return true;
        }

        $currentPhpDocReturnType = $this->getNodeReturnPhpDocType($classMethod);
        if ($currentPhpDocReturnType instanceof ArrayType && $currentPhpDocReturnType->getItemType() instanceof MixedType) {
            return true;
        }

        if ($this->hasInheritDoc($classMethod)) {
            return true;
        }

        return $currentPhpDocReturnType instanceof IterableType;
    }

    private function getNodeReturnPhpDocType(ClassMethod $classMethod): ?Type
    {
        /** @var PhpDocInfo|null $phpDocInfo */
        $phpDocInfo = $classMethod->getAttribute(AttributeKey::PHP_DOC_INFO);
        return $phpDocInfo !== null ? $phpDocInfo->getReturnType() : null;
    }

    /**
     * @deprecated
     * @todo merge to
     * @see \Rector\TypeDeclaration\TypeAlreadyAddedChecker\ReturnTypeAlreadyAddedChecker
     */
    private function shouldSkipType(Type $newType, ClassMethod $classMethod): bool
    {
        if ($newType instanceof ArrayType && $this->shouldSkipArrayType($newType, $classMethod)) {
            return true;
        }

        if ($newType instanceof UnionType && $this->shouldSkipUnionType($newType)) {
            return true;
        }

        // not an array type
        if ($newType instanceof VoidType) {
            return true;
        }

        if ($this->advancedArrayAnalyzer->isMoreSpecificArrayTypeOverride($newType, $classMethod)) {
            return true;
        }
        if (! $newType instanceof ConstantArrayType) {
            return false;
        }
        return count($newType->getValueTypes()) > self::MAX_NUMBER_OF_TYPES;
    }

    private function shouldSkipClassMethod(ClassMethod $classMethod): bool
    {
        if ($this->classMethodReturnTypeOverrideGuard->shouldSkipClassMethod($classMethod)) {
            return true;
        }

        if ($classMethod->returnType === null) {
            return false;
        }

        return ! $this->isNames($classMethod->returnType, ['array', 'iterable']);
    }

    private function shouldSkipArrayType(ArrayType $arrayType, ClassMethod $classMethod): bool
    {
        if ($this->advancedArrayAnalyzer->isNewAndCurrentTypeBothCallable($arrayType, $classMethod)) {
            return true;
        }

        if ($this->advancedArrayAnalyzer->isClassStringArrayByStringArrayOverride($arrayType, $classMethod)) {
            return true;
        }

        return $this->advancedArrayAnalyzer->isMixedOfSpecificOverride($arrayType, $classMethod);
    }

    private function shouldSkipUnionType(UnionType $unionType): bool
    {
        return count($unionType->getTypes()) > self::MAX_NUMBER_OF_TYPES;
    }

    private function hasArrayShapeNode(ClassMethod $classMethod): bool
    {
        /** @var PhpDocInfo|null $phpDocInfo */
        $phpDocInfo = $classMethod->getAttribute(AttributeKey::PHP_DOC_INFO);
        if (! $phpDocInfo instanceof PhpDocInfo) {
            return false;
        }

        $attributeAwareReturnTagValueNode = $phpDocInfo->getReturnTagValue();
        if (! $attributeAwareReturnTagValueNode instanceof ReturnTagValueNode) {
            return false;
        }

        if ($attributeAwareReturnTagValueNode->type instanceof AttributeAwareGenericTypeNode) {
            return true;
        }

        if ($attributeAwareReturnTagValueNode->type instanceof AttributeAwareArrayShapeNode) {
            return true;
        }

        if (! $attributeAwareReturnTagValueNode->type instanceof ArrayTypeNode) {
            return false;
        }

        return $attributeAwareReturnTagValueNode->type->type instanceof ArrayShapeNode;
    }

    private function hasInheritDoc(ClassMethod $classMethod): bool
    {
        $phpDocInfo = $classMethod->getAttribute(AttributeKey::PHP_DOC_INFO);
        if (! $phpDocInfo instanceof PhpDocInfo) {
            return false;
        }

        return $phpDocInfo->hasInheritDoc();
    }
}
