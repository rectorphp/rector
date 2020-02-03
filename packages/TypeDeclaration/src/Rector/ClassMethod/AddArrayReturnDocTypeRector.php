<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Type\ArrayType;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\IterableType;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use PHPStan\Type\VoidType;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;
use Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer;
use Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer\ReturnTypeDeclarationReturnTypeInferer;

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

    public function __construct(ReturnTypeInferer $returnTypeInferer)
    {
        $this->returnTypeInferer = $returnTypeInferer;
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

        /** @var PhpDocInfo $phpDocInfo */
        $phpDocInfo = $node->getAttribute(AttributeKey::PHP_DOC_INFO);
        $phpDocInfo->changeReturnType($inferedType);

        return $node;
    }

    private function shouldSkip(ClassMethod $classMethod): bool
    {
        if ($this->isName($classMethod->name, '__*')) {
            return true;
        }

        if ($classMethod->returnType === null) {
            return false;
        }

        if (! $this->isNames($classMethod->returnType, ['array', 'iterable'])) {
            return true;
        }

        $currentPhpDocInfo = $classMethod->getAttribute(AttributeKey::PHP_DOC_INFO);
        if ($currentPhpDocInfo === null) {
            return false;
        }

        $returnType = $currentPhpDocInfo->getReturnType();

        if ($returnType instanceof ArrayType && $returnType->getItemType() instanceof MixedType) {
            return true;
        }

        return $returnType instanceof IterableType;
    }

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

        return $newType instanceof ConstantArrayType && count($newType->getValueTypes()) > self::MAX_NUMBER_OF_TYPES;
    }

    private function shouldSkipArrayType(ArrayType $arrayType, ClassMethod $classMethod): bool
    {
        if ($this->isNewAndCurrentTypeBothCallable($arrayType, $classMethod)) {
            return true;
        }

        return $this->isMixedOfSpecificOverride($arrayType, $classMethod);
    }

    private function shouldSkipUnionType(UnionType $unionType): bool
    {
        return count($unionType->getTypes()) > self::MAX_NUMBER_OF_TYPES;
    }

    private function isNewAndCurrentTypeBothCallable(ArrayType $newArrayType, ClassMethod $classMethod): bool
    {
        $currentPhpDocInfo = $classMethod->getAttribute(AttributeKey::PHP_DOC_INFO);
        if ($currentPhpDocInfo === null) {
            return false;
        }

        $currentReturnType = $currentPhpDocInfo->getReturnType();
        if (! $currentReturnType instanceof ArrayType) {
            return false;
        }

        if (! $newArrayType->getItemType()->isCallable()->yes()) {
            return false;
        }

        return $currentReturnType->getItemType()->isCallable()->yes();
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
}
