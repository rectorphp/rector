<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Type\ArrayType;
use PHPStan\Type\ClassStringType;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\IterableType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NeverType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use PHPStan\Type\VoidType;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer;
use Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer\ReturnTypeDeclarationReturnTypeInferer;
use Rector\TypeDeclaration\TypeNormalizer;

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
     * @var TypeNormalizer
     */
    private $typeNormalizer;

    public function __construct(ReturnTypeInferer $returnTypeInferer, TypeNormalizer $typeNormalizer)
    {
        $this->returnTypeInferer = $returnTypeInferer;
        $this->typeNormalizer = $typeNormalizer;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Adds @return annotation to array parameters inferred from the rest of the code', [
            new CodeSample(
                <<<'PHP'
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
PHP
                ,
                <<<'PHP'
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
PHP
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

        /** @var PhpDocInfo|null $phpDocInfo */
        $phpDocInfo = $classMethod->getAttribute(AttributeKey::PHP_DOC_INFO);
        if ($phpDocInfo === null) {
            return false;
        }

        $returnType = $phpDocInfo->getReturnType();
        if ($returnType instanceof ArrayType && $returnType->getItemType() instanceof MixedType) {
            return true;
        }

        return $returnType instanceof IterableType;
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

        if ($this->isMoreSpecificArrayTypeOverride($newType, $classMethod)) {
            return true;
        }

        return $newType instanceof ConstantArrayType && count($newType->getValueTypes()) > self::MAX_NUMBER_OF_TYPES;
    }

    private function shouldSkipClassMethod(ClassMethod $classMethod): bool
    {
        if ($this->isName($classMethod->name, '__*')) {
            return true;
        }

        if ($classMethod->returnType === null) {
            return false;
        }

        return ! $this->isNames($classMethod->returnType, ['array', 'iterable']);
    }

    private function shouldSkipArrayType(ArrayType $arrayType, ClassMethod $classMethod): bool
    {
        if ($this->isNewAndCurrentTypeBothCallable($arrayType, $classMethod)) {
            return true;
        }

        if ($this->isClassStringArrayByStringArrayOverride($arrayType, $classMethod)) {
            return true;
        }

        return $this->isMixedOfSpecificOverride($arrayType, $classMethod);
    }

    private function shouldSkipUnionType(UnionType $unionType): bool
    {
        return count($unionType->getTypes()) > self::MAX_NUMBER_OF_TYPES;
    }

    private function isMoreSpecificArrayTypeOverride(Type $newType, ClassMethod $classMethod): bool
    {
        if (! $newType instanceof ConstantArrayType) {
            return false;
        }

        if (! $newType->getItemType() instanceof NeverType) {
            return false;
        }

        $phpDocReturnType = $this->getNodeReturnPhpDocType($classMethod);
        if (! $phpDocReturnType instanceof ArrayType) {
            return false;
        }

        return ! $phpDocReturnType->getItemType() instanceof VoidType;
    }

    private function isNewAndCurrentTypeBothCallable(ArrayType $newArrayType, ClassMethod $classMethod): bool
    {
        $currentReturnType = $this->getNodeReturnPhpDocType($classMethod);
        if (! $currentReturnType instanceof ArrayType) {
            return false;
        }

        if (! $newArrayType->getItemType()->isCallable()->yes()) {
            return false;
        }

        return $currentReturnType->getItemType()->isCallable()->yes();
    }

    private function isClassStringArrayByStringArrayOverride(ArrayType $arrayType, ClassMethod $classMethod): bool
    {
        if (! $arrayType instanceof ConstantArrayType) {
            return false;
        }

        $arrayType = $this->typeNormalizer->convertConstantArrayTypeToArrayType($arrayType);
        if ($arrayType === null) {
            return false;
        }

        $currentReturnType = $this->getNodeReturnPhpDocType($classMethod);
        if (! $currentReturnType instanceof ArrayType) {
            return false;
        }

        if (! $currentReturnType->getItemType() instanceof ClassStringType) {
            return false;
        }

        return $arrayType->getItemType() instanceof StringType;
    }

    private function isMixedOfSpecificOverride(ArrayType $arrayType, ClassMethod $classMethod): bool
    {
        if (! $arrayType->getItemType() instanceof MixedType) {
            return false;
        }

        $currentPhpDocInfo = $classMethod->getAttribute(AttributeKey::PHP_DOC_INFO);
        if ($currentPhpDocInfo === null) {
            return false;
        }

        return $currentPhpDocInfo->getReturnType() instanceof ArrayType;
    }

    private function getNodeReturnPhpDocType(ClassMethod $classMethod): ?Type
    {
        /** @var PhpDocInfo|null $phpDocInfo */
        $phpDocInfo = $classMethod->getAttribute(AttributeKey::PHP_DOC_INFO);
        if ($phpDocInfo === null) {
            return null;
        }

        return $phpDocInfo->getReturnType();
    }
}
