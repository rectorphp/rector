<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer;

use Nette\Utils\Strings;
use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PHPStan\PhpDocParser\Ast\Node as PhpDocParserNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocChildNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\Type\ArrayType;
use PHPStan\Type\BooleanType;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NeverType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use Rector\AttributeAwarePhpDoc\Ast\PhpDoc\AttributeAwarePhpDocTagNode;
use Rector\AttributeAwarePhpDoc\Ast\PhpDoc\AttributeAwareVarTagValueNode;
use Rector\AttributeAwarePhpDoc\Ast\Type\AttributeAwareIdentifierTypeNode;
use Rector\BetterPhpDocParser\Annotation\AnnotationNaming;
use Rector\BetterPhpDocParser\Ast\PhpDocNodeTraverser;
use Rector\BetterPhpDocParser\Attributes\Ast\AttributeAwareNodeFactory;
use Rector\BetterPhpDocParser\Attributes\Ast\PhpDoc\SpacelessPhpDocTagNode;
use Rector\BetterPhpDocParser\Contract\Doctrine\DoctrineRelationTagValueNodeInterface;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\PhpDocNode\AbstractTagValueNode;
use Rector\BetterPhpDocParser\Printer\PhpDocInfoPrinter;
use Rector\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\Exception\MissingTagException;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\PHPStan\TypeHasher;
use Rector\NodeTypeResolver\StaticTypeMapper;
use Rector\PHPStan\Type\AliasedObjectType;
use Rector\PHPStan\Type\ShortenedObjectType;

/**
 * @see \Rector\NodeTypeResolver\Tests\PhpDoc\NodeAnalyzer\DocBlockManipulatorTest
 */
final class DocBlockManipulator
{
    /**
     * @var PhpDocInfoPrinter
     */
    private $phpDocInfoPrinter;

    /**
     * @var AttributeAwareNodeFactory
     */
    private $attributeAwareNodeFactory;

    /**
     * @var PhpDocNodeTraverser
     */
    private $phpDocNodeTraverser;

    /**
     * @var StaticTypeMapper
     */
    private $staticTypeMapper;

    /**
     * @var bool
     */
    private $hasPhpDocChanged = false;

    /**
     * @var DocBlockClassRenamer
     */
    private $docBlockClassRenamer;

    /**
     * @var DocBlockNameImporter
     */
    private $docBlockNameImporter;

    /**
     * @var TypeHasher
     */
    private $typeHasher;

    /**
     * @var PhpDocInfoFactory
     */
    private $phpDocInfoFactory;

    public function __construct(
        PhpDocInfoPrinter $phpDocInfoPrinter,
        AttributeAwareNodeFactory $attributeAwareNodeFactory,
        PhpDocNodeTraverser $phpDocNodeTraverser,
        StaticTypeMapper $staticTypeMapper,
        DocBlockClassRenamer $docBlockClassRenamer,
        DocBlockNameImporter $docBlockNameImporter,
        TypeHasher $typeHasher,
        PhpDocInfoFactory $phpDocInfoFactory
    ) {
        $this->phpDocInfoPrinter = $phpDocInfoPrinter;
        $this->attributeAwareNodeFactory = $attributeAwareNodeFactory;
        $this->phpDocNodeTraverser = $phpDocNodeTraverser;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->docBlockClassRenamer = $docBlockClassRenamer;
        $this->docBlockNameImporter = $docBlockNameImporter;
        $this->typeHasher = $typeHasher;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
    }

    public function hasTag(Node $node, string $name): bool
    {
        if ($node->getDocComment() === null) {
            return false;
        }

        // simple check
        $pattern = '#@(\\\\)?' . preg_quote(ltrim($name, '@'), '#') . '#';
        if (Strings::match($node->getDocComment()->getText(), $pattern)) {
            return true;
        }

        // allow only class nodes further
        if (! class_exists($name)) {
            return false;
        }

        /** @var PhpDocInfo $phpDocInfo */
        $phpDocInfo = $node->getAttribute(AttributeKey::PHP_DOC_INFO);

        return $phpDocInfo->hasByType($name);
    }

    public function addTag(Node $node, PhpDocChildNode $phpDocChildNode): void
    {
        $phpDocChildNode = $this->attributeAwareNodeFactory->createFromNode($phpDocChildNode);

        /** @var PhpDocInfo|null $phpDocInfo */
        $phpDocInfo = $node->getAttribute(AttributeKey::PHP_DOC_INFO);
        if ($phpDocInfo === null) {
            $phpDocInfo = $this->phpDocInfoFactory->createFromNode($node);
        }

        $phpDocInfo->addPhpDocTagNode($phpDocChildNode);
    }

    public function addTagValueNodeWithShortName(Node $node, AbstractTagValueNode $tagValueNode): void
    {
        $spacelessPhpDocTagNode = new SpacelessPhpDocTagNode($tagValueNode::SHORT_NAME, $tagValueNode);
        $this->addTag($node, $spacelessPhpDocTagNode);
    }

    public function changeType(Node $node, Type $oldType, Type $newType): void
    {
        if (! $this->hasNodeTypeTags($node)) {
            return;
        }

        /** @var PhpDocInfo $phpDocInfo */
        $phpDocInfo = $node->getAttribute(AttributeKey::PHP_DOC_INFO);

        $this->docBlockClassRenamer->renamePhpDocType($phpDocInfo->getPhpDocNode(), $oldType, $newType, $node);
    }

    public function replaceAnnotationInNode(Node $node, string $oldAnnotation, string $newAnnotation): void
    {
        /** @var PhpDocInfo|null $phpDocInfo */
        $phpDocInfo = $node->getAttribute(AttributeKey::PHP_DOC_INFO);
        if ($phpDocInfo === null) {
            return;
        }

        $this->replaceTagByAnother($phpDocInfo->getPhpDocNode(), $oldAnnotation, $newAnnotation);
    }

    public function getReturnType(Node $node): Type
    {
        /** @var PhpDocInfo|null $phpDocInfo */
        $phpDocInfo = $node->getAttribute(AttributeKey::PHP_DOC_INFO);
        if ($phpDocInfo === null) {
            return new MixedType();
        }

        return $phpDocInfo->getReturnType();
    }

    /**
     * With "name" as key
     *
     * @param Function_|ClassMethod|Closure  $functionLike
     * @return Type[]
     */
    public function getParamTypesByName(FunctionLike $functionLike): array
    {
        /** @var PhpDocInfo|null $phpDocInfo */
        $phpDocInfo = $functionLike->getAttribute(AttributeKey::PHP_DOC_INFO);
        if ($phpDocInfo === null) {
            return [];
        }

        $paramTypesByName = [];

        foreach ($phpDocInfo->getParamTagValues() as $paramTagValueNode) {
            $parameterName = $paramTagValueNode->parameterName;

            $paramTypesByName[$parameterName] = $this->staticTypeMapper->mapPHPStanPhpDocTypeToPHPStanType(
                $paramTagValueNode,
                $functionLike
            );
        }

        return $paramTypesByName;
    }

    /**
     * @return PhpDocTagNode[]
     */
    public function getTagsByName(Node $node, string $name): array
    {
        /** @var PhpDocInfo|null $phpDocInfo */
        $phpDocInfo = $node->getAttribute(AttributeKey::PHP_DOC_INFO);
        if ($phpDocInfo === null) {
            return [];
        }

        return $phpDocInfo->getTagsByName($name);
    }

    public function changeVarTag(Node $node, Type $newType): void
    {
        $currentVarType = $this->getVarType($node);

        // make sure the tags are not identical, e.g imported class vs FQN class
        if ($this->areTypesEquals($currentVarType, $newType)) {
            return;
        }

        // prevent existing type override by mixed
        if (! $currentVarType instanceof MixedType && $newType instanceof ConstantArrayType && $newType->getItemType() instanceof NeverType) {
            return;
        }

        if ($this->hasTag($node, '@var')) {
            // just change the type
            $varTag = $this->getTagByName($node, '@var');

            /** @var VarTagValueNode $varTagValueNode */
            $varTagValueNode = $varTag->value;

            $phpDocType = $this->staticTypeMapper->mapPHPStanTypeToPHPStanPhpDocTypeNode($newType);
            $varTagValueNode->type = $phpDocType;
        } else {
            $this->addTypeSpecificTag($node, 'var', $newType);
        }

        // to invoke the node override
        $node->setAttribute(AttributeKey::ORIGINAL_NODE, null);
    }

    public function addReturnTag(Node $node, Type $newType): void
    {
        $currentReturnType = $this->getReturnType($node);

        // make sure the tags are not identical, e.g imported class vs FQN class
        if ($this->areTypesEquals($currentReturnType, $newType)) {
            return;
        }

        /** @var PhpDocInfo|null $phpDocInfo */
        $phpDocInfo = $node->getAttribute(AttributeKey::PHP_DOC_INFO);

        if ($phpDocInfo !== null) {
            $returnTagValueNode = $phpDocInfo->getByType(ReturnTagValueNode::class);

            // overide existing type
            if ($returnTagValueNode !== null) {
                $newPHPStanPhpDocType = $this->staticTypeMapper->mapPHPStanTypeToPHPStanPhpDocTypeNode($newType);
                $returnTagValueNode->type = $newPHPStanPhpDocType;

                return;
            }
        }

        $this->addTypeSpecificTag($node, 'return', $newType);
    }

    /**
     * @final
     */
    public function getTagByName(Node $node, string $name): PhpDocTagNode
    {
        if (! $this->hasTag($node, $name)) {
            throw new MissingTagException(sprintf('Tag "%s" was not found at "%s" node.', $name, get_class($node)));
        }

        /** @var PhpDocTagNode[] $foundTags */
        $foundTags = $this->getTagsByName($node, $name);
        return array_shift($foundTags);
    }

    public function getVarType(Node $node): Type
    {
        /** @var PhpDocInfo|null $phpDocInfo */
        $phpDocInfo = $node->getAttribute(AttributeKey::PHP_DOC_INFO);
        if ($phpDocInfo === null) {
            return new MixedType();
        }

        return $phpDocInfo->getVarType();
    }

    public function replaceTagByAnother(PhpDocNode $phpDocNode, string $oldTag, string $newTag): void
    {
        $oldTag = AnnotationNaming::normalizeName($oldTag);
        $newTag = AnnotationNaming::normalizeName($newTag);

        foreach ($phpDocNode->children as $phpDocChildNode) {
            if (! $phpDocChildNode instanceof PhpDocTagNode) {
                continue;
            }

            if ($phpDocChildNode->name === $oldTag) {
                $phpDocChildNode->name = $newTag;
            }
        }
    }

    public function importNames(Node $node): void
    {
        $phpDocInfo = $node->getAttribute(AttributeKey::PHP_DOC_INFO);
        if ($phpDocInfo === null) {
            return;
        }

        $this->docBlockNameImporter->importNames($phpDocInfo, $node);
    }

    /**
     * @param string[] $excludedClasses
     */
    public function changeUnderscoreType(Node $node, string $namespacePrefix, array $excludedClasses): void
    {
        $phpDocInfo = $node->getAttribute(AttributeKey::PHP_DOC_INFO);
        if ($phpDocInfo === null) {
            return;
        }

        $phpDocNode = $phpDocInfo->getPhpDocNode();
        $phpParserNode = $node;

        $this->phpDocNodeTraverser->traverseWithCallable($phpDocNode, function (PhpDocParserNode $node) use (
            $namespacePrefix,
            $excludedClasses,
            $phpParserNode
        ): PhpDocParserNode {
            if (! $node instanceof IdentifierTypeNode) {
                return $node;
            }

            $staticType = $this->staticTypeMapper->mapPHPStanPhpDocTypeNodeToPHPStanType($node, $phpParserNode);
            if (! $staticType instanceof ObjectType) {
                return $node;
            }

            if (! Strings::startsWith($staticType->getClassName(), $namespacePrefix)) {
                return $node;
            }

            // excluded?
            if (in_array($staticType->getClassName(), $excludedClasses, true)) {
                return $node;
            }

            // change underscore to \\
            $nameParts = explode('_', $staticType->getClassName());
            $node->name = '\\' . implode('\\', $nameParts);

            $this->hasPhpDocChanged = true;

            return $node;
        });

        if (! $this->hasPhpDocChanged) {
            return;
        }
    }

    /**
     * For better performance
     */
    public function hasNodeTypeTags(Node $node): bool
    {
        $docComment = $node->getDocComment();
        if ($docComment === null) {
            return false;
        }

        if ((bool) Strings::match($docComment->getText(), '#\@(param|throws|return|var)\b#')) {
            return true;
        }

        /** @var PhpDocInfo $phpDocInfo */
        $phpDocInfo = $node->getAttribute(AttributeKey::PHP_DOC_INFO);

        // has any type node?

        foreach ($phpDocInfo->getPhpDocNode()->children as $phpDocChildNode) {
            // is custom class, it can contain some type info
            if ($phpDocChildNode instanceof PhpDocTagNode && Strings::startsWith(
                get_class($phpDocChildNode->value),
                'Rector\\'
            )) {
                return true;
            }
        }

        return false;
    }

    public function updateNodeWithPhpDocInfo(Node $node, bool $shouldSkipEmptyLinesAbove = false): void
    {
        // nothing to change
        /** @var PhpDocInfo|null $phpDocInfo */
        $phpDocInfo = $node->getAttribute(AttributeKey::PHP_DOC_INFO);
        if ($phpDocInfo === null) {
            return;
        }

        // new node, needs to be reparsed
        if ($phpDocInfo->getPhpDocNode()->children !== [] && $phpDocInfo->getTokens() === []) {
            $phpDoc = $this->phpDocInfoPrinter->printPhpDocNode(
                $phpDocInfo->getPhpDocNode(),
                $shouldSkipEmptyLinesAbove
            );

            // slight correction
            if (Strings::match($phpDoc, '#^ * #m')) {
                $phpDoc = Strings::replace($phpDoc, '#\s+\*/$#m', "\n */");
            }
        } else {
            $phpDoc = $this->phpDocInfoPrinter->printFormatPreserving($phpDocInfo, $shouldSkipEmptyLinesAbove);
        }

        if ($phpDoc === '') {
            // no comments, null
            $node->setAttribute('comments', null);
            return;
        }

        // no change, don't save it
        // this is needed to prevent short classes override with FQN with same value → people don't like that for some reason

        if ($node->getDocComment() && $node->getDocComment()->getText() === $phpDoc) {
            return;
        }

        $node->setDocComment(new Doc($phpDoc));
    }

    public function getDoctrineFqnTargetEntity(Node $node): ?string
    {
        /** @var PhpDocInfo|null $phpDocInfo */
        $phpDocInfo = $node->getAttribute(AttributeKey::PHP_DOC_INFO);
        if ($phpDocInfo === null) {
            return null;
        }

        $relationTagValueNode = $phpDocInfo->getByType(DoctrineRelationTagValueNodeInterface::class);
        if ($relationTagValueNode === null) {
            return null;
        }

        return $relationTagValueNode->getFqnTargetEntity();
    }

    public function getParamTypeByName(FunctionLike $functionLike, string $paramName): Type
    {
        $this->ensureParamNameStartsWithDollar($paramName, __METHOD__);

        $paramTypes = $this->getParamTypesByName($functionLike);
        return $paramTypes[$paramName] ?? new MixedType();
    }

    /**
     * @todo Extract this logic to own service
     */
    private function areTypesEquals(Type $firstType, Type $secondType): bool
    {
        if ($this->areBothSameScalarType($firstType, $secondType)) {
            return true;
        }

        // aliases and types
        if ($this->areAliasedObjectMatchingFqnObject($firstType, $secondType)) {
            return true;
        }

        if ($this->typeHasher->areTypesEqual($firstType, $secondType)) {
            return true;
        }

        return $this->areArrayTypeWithSingleObjectChildToParent($firstType, $secondType);
    }

    /**
     * All class-type tags are FQN by default to keep default convention through the code.
     * Some people prefer FQN, some short. FQN can be shorten with \Rector\CodingStyle\Rector\Namespace_\ImportFullyQualifiedNamesRector later, while short prolonged not
     */
    private function addTypeSpecificTag(Node $node, string $name, Type $type): void
    {
        $docStringType = $this->staticTypeMapper->mapPHPStanTypeToDocString($type);
        if ($docStringType === '') {
            return;
        }

        /** @var PhpDocInfo|null $phpDocInfo */
        $phpDocInfo = $node->getAttribute(AttributeKey::PHP_DOC_INFO);

        // there might be no phpdoc at all
        if ($phpDocInfo !== null) {
            $varTagValueNode = new AttributeAwareVarTagValueNode(new AttributeAwareIdentifierTypeNode(
                $docStringType
            ), '', '');

            $varTagValueNode = new AttributeAwarePhpDocTagNode('@' . $name, $varTagValueNode);
            $phpDocInfo->addPhpDocTagNode($varTagValueNode);
        } else {
            // create completely new docblock
            $varDocComment = sprintf("/**\n * @%s %s\n */", $name, $docStringType);
            $node->setDocComment(new Doc($varDocComment));
        }
    }

    private function ensureParamNameStartsWithDollar(string $paramName, string $location): void
    {
        if (Strings::startsWith($paramName, '$')) {
            return;
        }

        throw new ShouldNotHappenException(sprintf(
            'Param name "%s" must start with "$" in "%s()" method.',
            $paramName,
            $location
        ));
    }

    /**
     * E.g.  class A extends B, class B → A[] is subtype of B[] → keep A[]
     */
    private function areArrayTypeWithSingleObjectChildToParent(Type $firstType, Type $secondType): bool
    {
        if (! $firstType instanceof ArrayType || ! $secondType instanceof ArrayType) {
            return false;
        }

        $firstArrayItemType = $firstType->getItemType();
        $secondArrayItemType = $secondType->getItemType();

        if ($firstArrayItemType instanceof ObjectType && $secondArrayItemType instanceof ObjectType) {
            $firstFqnClassName = $this->getFqnClassName($firstArrayItemType);
            $secondFqnClassName = $this->getFqnClassName($secondArrayItemType);

            if (is_a($firstFqnClassName, $secondFqnClassName, true)) {
                return true;
            }

            if (is_a($secondFqnClassName, $firstFqnClassName, true)) {
                return true;
            }
        }

        return false;
    }

    private function getFqnClassName(ObjectType $objectType): string
    {
        if ($objectType instanceof ShortenedObjectType) {
            return $objectType->getFullyQualifiedName();
        }

        return $objectType->getClassName();
    }

    private function areBothSameScalarType(Type $firstType, Type $secondType): bool
    {
        if ($firstType instanceof StringType && $secondType instanceof StringType) {
            return true;
        }

        if ($firstType instanceof IntegerType && $secondType instanceof IntegerType) {
            return true;
        }

        if ($firstType instanceof FloatType && $secondType instanceof FloatType) {
            return true;
        }
        return $firstType instanceof BooleanType && $secondType instanceof BooleanType;
    }

    private function areAliasedObjectMatchingFqnObject(Type $firstType, Type $secondType): bool
    {
        if ($firstType instanceof AliasedObjectType && $secondType instanceof ObjectType && $firstType->getFullyQualifiedClass() === $secondType->getClassName()) {
            return true;
        }
        return $secondType instanceof AliasedObjectType && $firstType instanceof ObjectType && $secondType->getFullyQualifiedClass() === $firstType->getClassName();
    }
}
