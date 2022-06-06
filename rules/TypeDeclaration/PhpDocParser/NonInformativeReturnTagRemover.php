<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\TypeDeclaration\PhpDocParser;

use RectorPrefix20220606\PhpParser\Node\Stmt\ClassMethod;
use RectorPrefix20220606\PhpParser\Node\Stmt\Function_;
use RectorPrefix20220606\PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use RectorPrefix20220606\PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use RectorPrefix20220606\PHPStan\PhpDocParser\Ast\Type\TypeNode;
use RectorPrefix20220606\PHPStan\PhpDocParser\Ast\Type\UnionTypeNode;
use RectorPrefix20220606\PHPStan\Type\ArrayType;
use RectorPrefix20220606\PHPStan\Type\BooleanType;
use RectorPrefix20220606\PHPStan\Type\CallableType;
use RectorPrefix20220606\PHPStan\Type\FloatType;
use RectorPrefix20220606\PHPStan\Type\IntegerType;
use RectorPrefix20220606\PHPStan\Type\IterableType;
use RectorPrefix20220606\PHPStan\Type\NullType;
use RectorPrefix20220606\PHPStan\Type\ObjectWithoutClassType;
use RectorPrefix20220606\PHPStan\Type\StringType;
use RectorPrefix20220606\PHPStan\Type\Type;
use RectorPrefix20220606\PHPStan\Type\UnionType;
use RectorPrefix20220606\PHPStan\Type\VoidType;
use RectorPrefix20220606\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use RectorPrefix20220606\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use RectorPrefix20220606\Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
use RectorPrefix20220606\Rector\StaticTypeMapper\ValueObject\Type\ParentStaticType;
use RectorPrefix20220606\Rector\StaticTypeMapper\ValueObject\Type\SelfObjectType;
use RectorPrefix20220606\Rector\StaticTypeMapper\ValueObject\Type\ShortenedObjectType;
use RectorPrefix20220606\Symplify\PackageBuilder\Php\TypeChecker;
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
     * @var \Symplify\PackageBuilder\Php\TypeChecker
     */
    private $typeChecker;
    public function __construct(PhpDocInfoFactory $phpDocInfoFactory, TypeChecker $typeChecker)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->typeChecker = $typeChecker;
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
        if ($this->typeChecker->isInstanceOf($returnType, [FloatType::class, StringType::class, IntegerType::class])) {
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
        $nullabledReturnTagValueNode = $this->matchNullabledReturnTagValueNode($returnTagValueNode);
        if (!$nullabledReturnTagValueNode instanceof TypeNode) {
            return;
        }
        if (!$nullabledReturnType instanceof FullyQualifiedObjectType) {
            return;
        }
        if (!$nullabledReturnTagValueNode instanceof IdentifierTypeNode) {
            return;
        }
        if (\substr_compare($nullabledReturnType->getClassName(), $nullabledReturnTagValueNode->name, -\strlen($nullabledReturnTagValueNode->name)) !== 0) {
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
        if (!$returnType instanceof UnionType) {
            return null;
        }
        if (!$returnType->isSuperTypeOf(new NullType())->yes()) {
            return null;
        }
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
