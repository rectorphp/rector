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
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
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
use Rector\AttributeAwarePhpDoc\Ast\PhpDoc\AttributeAwarePhpDocNode;
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
use Rector\NodeTypeResolver\StaticTypeMapper;
use Rector\PHPStan\Type\AliasedObjectType;
use Rector\PHPStan\Type\ShortenedObjectType;

/**
 * @see \Rector\NodeTypeResolver\Tests\PhpDoc\NodeAnalyzer\DocBlockManipulatorTest
 */
final class DocBlockManipulator
{
    /**
     * @var PhpDocInfoFactory
     */
    private $phpDocInfoFactory;

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

    public function __construct(
        PhpDocInfoFactory $phpDocInfoFactory,
        PhpDocInfoPrinter $phpDocInfoPrinter,
        AttributeAwareNodeFactory $attributeAwareNodeFactory,
        PhpDocNodeTraverser $phpDocNodeTraverser,
        StaticTypeMapper $staticTypeMapper,
        DocBlockClassRenamer $docBlockClassRenamer,
        DocBlockNameImporter $docBlockNameImporter
    ) {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->phpDocInfoPrinter = $phpDocInfoPrinter;
        $this->attributeAwareNodeFactory = $attributeAwareNodeFactory;
        $this->phpDocNodeTraverser = $phpDocNodeTraverser;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->docBlockClassRenamer = $docBlockClassRenamer;
        $this->docBlockNameImporter = $docBlockNameImporter;
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

        // advanced check, e.g. for "Namespaced\Annotations\DI"
        $phpDocInfo = $this->createPhpDocInfoFromNode($node);

        return $phpDocInfo->hasByType($name);
    }

    public function addTag(Node $node, PhpDocChildNode $phpDocChildNode): void
    {
        $phpDocChildNode = $this->attributeAwareNodeFactory->createFromNode($phpDocChildNode);

        if ($node->getDocComment() !== null) {
            $phpDocInfo = $this->createPhpDocInfoFromNode($node);
            $phpDocNode = $phpDocInfo->getPhpDocNode();
            $phpDocNode->children[] = $phpDocChildNode;
            $this->updateNodeWithPhpDocInfo($node, $phpDocInfo);
        } else {
            $phpDocNode = new AttributeAwarePhpDocNode([$phpDocChildNode]);
            $node->setDocComment(new Doc($phpDocNode->__toString()));
        }
    }

    public function addTagValueNodeWithShortName(Node $node, AbstractTagValueNode $tagValueNode): void
    {
        $spacelessPhpDocTagNode = new SpacelessPhpDocTagNode($tagValueNode::SHORT_NAME, $tagValueNode);
        $this->addTag($node, $spacelessPhpDocTagNode);
    }

    public function removeTagFromNode(Node $node, string $name, bool $shouldSkipEmptyLinesAbove = false): void
    {
        if ($node->getDocComment() === null) {
            return;
        }

        $phpDocInfo = $this->createPhpDocInfoFromNode($node);

        $this->removeTagByName($phpDocInfo, $name);
        $this->updateNodeWithPhpDocInfo($node, $phpDocInfo, $shouldSkipEmptyLinesAbove);
    }

    public function changeType(Node $node, Type $oldType, Type $newType): void
    {
        if (! $this->hasNodeTypeTags($node)) {
            return;
        }

        $phpDocInfo = $this->createPhpDocInfoFromNode($node);
        $hasNodeChanged = $this->docBlockClassRenamer->renamePhpDocType(
            $phpDocInfo->getPhpDocNode(),
            $oldType,
            $newType,
            $node
        );

        if ($hasNodeChanged) {
            $this->updateNodeWithPhpDocInfo($node, $phpDocInfo);
        }
    }

    public function replaceAnnotationInNode(Node $node, string $oldAnnotation, string $newAnnotation): void
    {
        if ($node->getDocComment() === null) {
            return;
        }

        $phpDocInfo = $this->createPhpDocInfoFromNode($node);
        $this->replaceTagByAnother($phpDocInfo->getPhpDocNode(), $oldAnnotation, $newAnnotation);

        $this->updateNodeWithPhpDocInfo($node, $phpDocInfo);
    }

    public function getReturnType(Node $node): Type
    {
        if ($node->getDocComment() === null) {
            return new MixedType();
        }

        $phpDocInfo = $this->createPhpDocInfoFromNode($node);

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
        if ($functionLike->getDocComment() === null) {
            return [];
        }

        $phpDocInfo = $this->createPhpDocInfoFromNode($functionLike);

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
     * @final
     * @return PhpDocTagNode[]
     */
    public function getTagsByName(Node $node, string $name): array
    {
        if ($node->getDocComment() === null) {
            return [];
        }

        $phpDocInfo = $this->createPhpDocInfoFromNode($node);

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

            // update doc :)
            $phpDocInfo = $this->createPhpDocInfoFromNode($node);
            $this->updateNodeWithPhpDocInfo($node, $phpDocInfo);
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

        if ($node->getDocComment() !== null) {
            $phpDocInfo = $this->createPhpDocInfoFromNode($node);
            $returnTagValueNode = $phpDocInfo->getByType(ReturnTagValueNode::class);

            // overide existing type
            if ($returnTagValueNode !== null) {
                $newPHPStanPhpDocType = $this->staticTypeMapper->mapPHPStanTypeToPHPStanPhpDocTypeNode($newType);
                $returnTagValueNode->type = $newPHPStanPhpDocType;

                $this->updateNodeWithPhpDocInfo($node, $phpDocInfo);
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
        if ($node->getDocComment() === null) {
            return new MixedType();
        }

        $phpDocInfo = $this->createPhpDocInfoFromNode($node);

        return $phpDocInfo->getVarType();
    }

    public function removeTagByName(PhpDocInfo $phpDocInfo, string $tagName): void
    {
        $phpDocNode = $phpDocInfo->getPhpDocNode();

        // A. remove class-based tag
        if (class_exists($tagName)) {
            $phpDocInfo->removeByType($tagName);
        }

        // B. remove string-based tags
        $tagName = AnnotationNaming::normalizeName($tagName);
        $phpDocTagNodes = $phpDocInfo->getTagsByName($tagName);
        foreach ($phpDocTagNodes as $phpDocTagNode) {
            $this->removeTagFromPhpDocNode($phpDocNode, $phpDocTagNode);
        }
    }

    /**
     * @param PhpDocTagNode|PhpDocTagValueNode $phpDocTagOrPhpDocTagValueNode
     */
    public function removeTagFromPhpDocNode(PhpDocNode $phpDocNode, $phpDocTagOrPhpDocTagValueNode): void
    {
        // remove specific tag
        foreach ($phpDocNode->children as $key => $phpDocChildNode) {
            if ($phpDocChildNode === $phpDocTagOrPhpDocTagValueNode) {
                unset($phpDocNode->children[$key]);
                return;
            }
        }

        // or by type
        foreach ($phpDocNode->children as $key => $phpDocChildNode) {
            if (! $phpDocChildNode instanceof PhpDocTagNode) {
                continue;
            }

            if ($phpDocChildNode->value === $phpDocTagOrPhpDocTagValueNode) {
                unset($phpDocNode->children[$key]);
            }
        }
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
        if ($node->getDocComment() === null) {
            return;
        }

        $phpDocInfo = $this->createPhpDocInfoFromNode($node);
        $hasNodeChanged = $this->docBlockNameImporter->importNames($phpDocInfo, $node);

        if ($hasNodeChanged) {
            $this->updateNodeWithPhpDocInfo($node, $phpDocInfo);
        }
    }

    /**
     * @param string[] $excludedClasses
     */
    public function changeUnderscoreType(Node $node, string $namespacePrefix, array $excludedClasses): void
    {
        if ($node->getDocComment() === null) {
            return;
        }

        $phpDocInfo = $this->createPhpDocInfoFromNode($node);
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

        $this->updateNodeWithPhpDocInfo($node, $phpDocInfo);
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

        $phpDocInfo = $this->createPhpDocInfoFromNode($node);

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

    public function updateNodeWithPhpDocInfo(
        Node $node,
        PhpDocInfo $phpDocInfo,
        bool $shouldSkipEmptyLinesAbove = false
    ): bool {
        $phpDoc = $this->phpDocInfoPrinter->printFormatPreserving($phpDocInfo, $shouldSkipEmptyLinesAbove);
        if ($phpDoc !== '') {
            // no change, don't save it
            if ($node->getDocComment() && $node->getDocComment()->getText() === $phpDoc) {
                return false;
            }

            $node->setDocComment(new Doc($phpDoc));
            return true;
        }

        // no comments, null
        $node->setAttribute('comments', null);

        return true;
    }

    public function getDoctrineFqnTargetEntity(Node $node): ?string
    {
        if ($node->getDocComment() === null) {
            return null;
        }

        $phpDocInfo = $this->createPhpDocInfoFromNode($node);

        $relationTagValueNode = $phpDocInfo->getByType(DoctrineRelationTagValueNodeInterface::class);
        if ($relationTagValueNode === null) {
            return null;
        }

        return $relationTagValueNode->getFqnTargetEntity();
    }

    public function createPhpDocInfoFromNode(Node $node): PhpDocInfo
    {
        if ($node->getDocComment() === null) {
            throw new ShouldNotHappenException(sprintf(
                'Node must have a comment. Check `$node->getDocComment() !== null` before passing it to %s',
                __METHOD__
            ));
        }

        return $this->phpDocInfoFactory->createFromNode($node);
    }

    public function getParamTypeByName(FunctionLike $functionLike, string $paramName): Type
    {
        $this->ensureParamNameStartsWithDollar($paramName, __METHOD__);

        $paramTypes = $this->getParamTypesByName($functionLike);
        return $paramTypes[$paramName] ?? new MixedType();
    }

    public function isInheritdoc(Node $node): bool
    {
        /** @var Doc $attribute */
        $attribute = $node->getAttribute('comments')[0];
        $attributeText = $attribute !== null ? $attribute->getText() : '';

        return Strings::contains(Strings::lower($attributeText), '@inheritdoc');
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

        $firstTypeHash = $this->staticTypeMapper->createTypeHash($firstType);
        $secondTypeHash = $this->staticTypeMapper->createTypeHash($secondType);

        if ($firstTypeHash === $secondTypeHash) {
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

        // there might be no phpdoc at all
        if ($node->getDocComment() !== null) {
            $phpDocInfo = $this->createPhpDocInfoFromNode($node);
            $phpDocNode = $phpDocInfo->getPhpDocNode();

            $varTagValueNode = new AttributeAwareVarTagValueNode(new AttributeAwareIdentifierTypeNode(
                $docStringType
            ), '', '');
            $phpDocNode->children[] = new AttributeAwarePhpDocTagNode('@' . $name, $varTagValueNode);

            $this->updateNodeWithPhpDocInfo($node, $phpDocInfo);
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
