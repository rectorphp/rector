<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\PhpDocParser;

use Nette\Utils\Strings;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\PhpDocParser\Ast\Type\UnionTypeNode;
use PHPStan\Type\ArrayType;
use PHPStan\Type\BooleanType;
use PHPStan\Type\CallableType;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\IterableType;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectWithoutClassType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use PHPStan\Type\VoidType;
use Rector\AttributeAwarePhpDoc\Ast\PhpDoc\AttributeAwareReturnTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
use Rector\StaticTypeMapper\ValueObject\Type\ParentStaticType;
use Rector\StaticTypeMapper\ValueObject\Type\SelfObjectType;
use Rector\StaticTypeMapper\ValueObject\Type\ShortenedObjectType;
use Symplify\PackageBuilder\Php\TypeChecker;

final class NonInformativeReturnTagRemover
{
    /**
     * @var string[][]
     */
    private const USELESS_DOC_NAMES_BY_TYPE_CLASS = [
        IterableType::class => ['iterable'],
        CallableType::class => ['callable'],
        VoidType::class => ['void'],
        ArrayType::class => ['array'],
        SelfObjectType::class => ['self'],
        ParentStaticType::class => ['parent'],
        BooleanType::class => ['bool', 'boolean'],
        ObjectWithoutClassType::class => ['object'],
    ];

    /**
     * @var PhpDocInfoFactory
     */
    private $phpDocInfoFactory;

    /**
     * @var TypeChecker
     */
    private $typeChecker;

    public function __construct(PhpDocInfoFactory $phpDocInfoFactory, TypeChecker $typeChecker)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->typeChecker = $typeChecker;
    }

    /**
     * @param ClassMethod|Function_ $functionLike
     */
    public function removeReturnTagIfNotUseful(FunctionLike $functionLike): void
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($functionLike);

        $attributeAwareReturnTagValueNode = $phpDocInfo->getReturnTagValue();
        if (! $attributeAwareReturnTagValueNode instanceof ReturnTagValueNode) {
            return;
        }

        // useful
        if ($attributeAwareReturnTagValueNode->description !== '') {
            return;
        }

        $returnType = $phpDocInfo->getReturnType();

        // is bare type
        if ($this->typeChecker->isInstanceOf($returnType, [FloatType::class, StringType::class, IntegerType::class])) {
            $phpDocInfo->removeByType(ReturnTagValueNode::class);
            return;
        }

        $this->removeNonUniqueUselessDocNames($returnType, $attributeAwareReturnTagValueNode, $phpDocInfo);
        $this->removeShortObjectType($returnType, $attributeAwareReturnTagValueNode, $phpDocInfo);
        $this->removeNullableType($returnType, $attributeAwareReturnTagValueNode, $phpDocInfo);
        $this->removeFullyQualifiedObjectType($returnType, $attributeAwareReturnTagValueNode, $phpDocInfo);
    }

    private function removeNonUniqueUselessDocNames(
        Type $returnType,
        AttributeAwareReturnTagValueNode $attributeAwareReturnTagValueNode,
        PhpDocInfo $phpDocInfo
    ): void {
        foreach (self::USELESS_DOC_NAMES_BY_TYPE_CLASS as $typeClass => $uselessDocNames) {
            if (! is_a($returnType, $typeClass, true)) {
                continue;
            }

            if (! $this->isIdentifierWithValues($attributeAwareReturnTagValueNode->type, $uselessDocNames)) {
                continue;
            }

            $phpDocInfo->removeByType(ReturnTagValueNode::class);
            return;
        }
    }

    private function removeShortObjectType(
        Type $returnType,
        AttributeAwareReturnTagValueNode $attributeAwareReturnTagValueNode,
        PhpDocInfo $phpDocInfo
    ): void {
        if (! $returnType instanceof ShortenedObjectType) {
            return;
        }

        if (! $this->isIdentifierWithValues($attributeAwareReturnTagValueNode->type, [$returnType->getShortName()])) {
            return;
        }

        $phpDocInfo->removeByType(ReturnTagValueNode::class);
    }

    private function removeNullableType(
        Type $returnType,
        AttributeAwareReturnTagValueNode $attributeAwareReturnTagValueNode,
        PhpDocInfo $phpDocInfo
    ): void {
        $nullabledReturnType = $this->matchNullabledType($returnType);
        if (! $nullabledReturnType instanceof Type) {
            return;
        }

        $nullabledReturnTagValueNode = $this->matchNullabledReturnTagValueNode($attributeAwareReturnTagValueNode);
        if (! $nullabledReturnTagValueNode instanceof TypeNode) {
            return;
        }

        if (! $nullabledReturnType instanceof FullyQualifiedObjectType) {
            return;
        }

        if (! $nullabledReturnTagValueNode instanceof IdentifierTypeNode) {
            return;
        }

        if (! Strings::endsWith($nullabledReturnType->getClassName(), $nullabledReturnTagValueNode->name)) {
            return;
        }

        $phpDocInfo->removeByType(ReturnTagValueNode::class);
    }

    private function removeFullyQualifiedObjectType(
        Type $returnType,
        AttributeAwareReturnTagValueNode $attributeAwareReturnTagValueNode,
        PhpDocInfo $phpDocInfo
    ): void {
        if (! $returnType instanceof FullyQualifiedObjectType) {
            return;
        }

        if (! $attributeAwareReturnTagValueNode->type instanceof IdentifierTypeNode) {
            return;
        }

        $className = $returnType->getClassName();
        $returnTagValueNodeType = (string) $attributeAwareReturnTagValueNode->type;

        if ($this->isClassNameAndPartMatch($className, $returnTagValueNodeType)) {
            $phpDocInfo->removeByType(ReturnTagValueNode::class);
        }
    }

    /**
     * @param string[] $values
     */
    private function isIdentifierWithValues(TypeNode $typeNode, array $values): bool
    {
        if (! $typeNode instanceof IdentifierTypeNode) {
            return false;
        }

        return in_array($typeNode->name, $values, true);
    }

    private function matchNullabledType(Type $returnType): ?Type
    {
        if (! $returnType instanceof UnionType) {
            return null;
        }

        if (! $returnType->isSuperTypeOf(new NullType())->yes()) {
            return null;
        }

        if (count($returnType->getTypes()) !== 2) {
            return null;
        }

        foreach ($returnType->getTypes() as $unionedReturnType) {
            if ($unionedReturnType instanceof NullType) {
                continue;
            }

            return $unionedReturnType;
        }

        return null;
    }

    private function matchNullabledReturnTagValueNode(
        AttributeAwareReturnTagValueNode $attributeAwareReturnTagValueNode
    ): ?TypeNode {
        if (! $attributeAwareReturnTagValueNode->type instanceof UnionTypeNode) {
            return null;
        }

        if (count($attributeAwareReturnTagValueNode->type->types) !== 2) {
            return null;
        }

        foreach ($attributeAwareReturnTagValueNode->type->types as $unionedReturnTagValueNode) {
            if ($this->isIdentifierWithValues($unionedReturnTagValueNode, ['null'])) {
                continue;
            }

            return $unionedReturnTagValueNode;
        }

        return null;
    }

    private function isClassNameAndPartMatch(string $className, string $returnTagValueNodeType): bool
    {
        if ($className === $returnTagValueNodeType) {
            return true;
        }

        if ('\\' . $className === $returnTagValueNodeType) {
            return true;
        }

        return Strings::endsWith($className, '\\' . $returnTagValueNodeType);
    }
}
