<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\PhpDocParser;

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
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\UnionType;
use PHPStan\Type\VoidType;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Core\Util\MultiInstanceofChecker;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
use Rector\StaticTypeMapper\ValueObject\Type\ParentStaticType;
use Rector\StaticTypeMapper\ValueObject\Type\SelfObjectType;
use Rector\StaticTypeMapper\ValueObject\Type\ShortenedObjectType;
final class NonInformativeReturnTagRemover
{
    /**
     * @var array<class-string<Type>, string[]>
     */
    private const USELESS_DOC_NAMES_BY_TYPE_CLASS = [IterableType::class => ['iterable'], CallableType::class => ['callable'], VoidType::class => ['void'], ArrayType::class => ['array'], SelfObjectType::class => ['self'], ParentStaticType::class => ['parent'], BooleanType::class => ['bool', 'boolean'], ObjectWithoutClassType::class => ['object']];
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    /**
     * @readonly
     * @var \Rector\Core\Util\MultiInstanceofChecker
     */
    private $multiInstanceofChecker;
    public function __construct(PhpDocInfoFactory $phpDocInfoFactory, MultiInstanceofChecker $multiInstanceofChecker)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->multiInstanceofChecker = $multiInstanceofChecker;
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_ $functionLike
     */
    public function removeReturnTagIfNotUseful($functionLike) : void
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($functionLike);
        $returnTagValueNode = $phpDocInfo->getReturnTagValue();
        if (!$returnTagValueNode instanceof ReturnTagValueNode) {
            return;
        }
        // useful
        if ($returnTagValueNode->description !== '') {
            return;
        }
        $returnType = $phpDocInfo->getReturnType();
        // is bare type
        if ($this->multiInstanceofChecker->isInstanceOf($returnType, [FloatType::class, StringType::class, IntegerType::class])) {
            $phpDocInfo->removeByType(ReturnTagValueNode::class);
            return;
        }
        $this->removeNonUniqueUselessDocNames($returnType, $returnTagValueNode, $phpDocInfo);
        $this->removeShortObjectType($returnType, $returnTagValueNode, $phpDocInfo);
        $this->removeNullableType($returnType, $returnTagValueNode, $phpDocInfo);
        $this->removeFullyQualifiedObjectType($returnType, $returnTagValueNode, $phpDocInfo);
    }
    private function removeNonUniqueUselessDocNames(Type $returnType, ReturnTagValueNode $returnTagValueNode, PhpDocInfo $phpDocInfo) : void
    {
        foreach (self::USELESS_DOC_NAMES_BY_TYPE_CLASS as $typeClass => $uselessDocNames) {
            if (!\is_a($returnType, $typeClass, \true)) {
                continue;
            }
            if (!$this->isIdentifierWithValues($returnTagValueNode->type, $uselessDocNames)) {
                continue;
            }
            $phpDocInfo->removeByType(ReturnTagValueNode::class);
            return;
        }
    }
    private function removeShortObjectType(Type $returnType, ReturnTagValueNode $returnTagValueNode, PhpDocInfo $phpDocInfo) : void
    {
        if (!$returnType instanceof ShortenedObjectType) {
            return;
        }
        if (!$this->isIdentifierWithValues($returnTagValueNode->type, [$returnType->getShortName()])) {
            return;
        }
        $phpDocInfo->removeByType(ReturnTagValueNode::class);
    }
    private function removeNullableType(Type $returnType, ReturnTagValueNode $returnTagValueNode, PhpDocInfo $phpDocInfo) : void
    {
        $nullabledReturnType = $this->matchNullabledType($returnType);
        if (!$nullabledReturnType instanceof Type) {
            return;
        }
        $nullableTypeNode = $this->matchNullabledReturnTagValueNode($returnTagValueNode);
        if (!$nullableTypeNode instanceof TypeNode) {
            return;
        }
        if (!$nullabledReturnType instanceof FullyQualifiedObjectType) {
            return;
        }
        if (!$nullableTypeNode instanceof IdentifierTypeNode) {
            return;
        }
        if (\substr_compare($nullabledReturnType->getClassName(), $nullableTypeNode->name, -\strlen($nullableTypeNode->name)) !== 0) {
            return;
        }
        $phpDocInfo->removeByType(ReturnTagValueNode::class);
    }
    private function removeFullyQualifiedObjectType(Type $returnType, ReturnTagValueNode $returnTagValueNode, PhpDocInfo $phpDocInfo) : void
    {
        if (!$returnType instanceof FullyQualifiedObjectType) {
            return;
        }
        if (!$returnTagValueNode->type instanceof IdentifierTypeNode) {
            return;
        }
        $className = $returnType->getClassName();
        $returnTagValueNodeType = (string) $returnTagValueNode->type;
        if ($this->isClassNameAndPartMatch($className, $returnTagValueNodeType)) {
            $phpDocInfo->removeByType(ReturnTagValueNode::class);
        }
    }
    /**
     * @param string[] $values
     */
    private function isIdentifierWithValues(TypeNode $typeNode, array $values) : bool
    {
        if (!$typeNode instanceof IdentifierTypeNode) {
            return \false;
        }
        return \in_array($typeNode->name, $values, \true);
    }
    private function matchNullabledType(Type $returnType) : ?Type
    {
        if (!TypeCombinator::containsNull($returnType)) {
            return null;
        }
        /** @var UnionType $returnType */
        if (\count($returnType->getTypes()) !== 2) {
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
    private function matchNullabledReturnTagValueNode(ReturnTagValueNode $returnTagValueNode) : ?TypeNode
    {
        if (!$returnTagValueNode->type instanceof UnionTypeNode) {
            return null;
        }
        if (\count($returnTagValueNode->type->types) !== 2) {
            return null;
        }
        foreach ($returnTagValueNode->type->types as $unionedReturnTagValueNode) {
            if ($this->isIdentifierWithValues($unionedReturnTagValueNode, ['null'])) {
                continue;
            }
            return $unionedReturnTagValueNode;
        }
        return null;
    }
    private function isClassNameAndPartMatch(string $className, string $returnTagValueNodeType) : bool
    {
        if ($className === $returnTagValueNodeType) {
            return \true;
        }
        if ('\\' . $className === $returnTagValueNodeType) {
            return \true;
        }
        return \substr_compare($className, '\\' . $returnTagValueNodeType, -\strlen('\\' . $returnTagValueNodeType)) === 0;
    }
}
