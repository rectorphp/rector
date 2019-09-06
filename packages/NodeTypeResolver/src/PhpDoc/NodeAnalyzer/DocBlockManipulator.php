<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer;

use Nette\Utils\Strings;
use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PHPStan\PhpDocParser\Ast\Node as PhpDocParserNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocChildNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\Annotation\AnnotationNaming;
use Rector\BetterPhpDocParser\Ast\NodeTraverser;
use Rector\BetterPhpDocParser\Attributes\Ast\AttributeAwareNodeFactory;
use Rector\BetterPhpDocParser\Attributes\Ast\PhpDoc\AttributeAwarePhpDocNode;
use Rector\BetterPhpDocParser\Attributes\Ast\PhpDoc\AttributeAwarePhpDocTagNode;
use Rector\BetterPhpDocParser\Attributes\Ast\PhpDoc\AttributeAwareVarTagValueNode;
use Rector\BetterPhpDocParser\Attributes\Ast\PhpDoc\Type\AttributeAwareIdentifierTypeNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\Printer\PhpDocInfoPrinter;
use Rector\CodingStyle\Application\UseAddingCommander;
use Rector\DoctrinePhpDocParser\Contract\Ast\PhpDoc\DoctrineRelationTagValueNodeInterface;
use Rector\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\Exception\MissingTagException;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\StaticTypeMapper;
use Rector\PhpParser\Node\Resolver\NameResolver;
use Rector\PhpParser\Printer\BetterStandardPrinter;
use Rector\PHPStan\Type\FullyQualifiedObjectType;
use Rector\PHPStan\Type\ShortenedObjectType;

/**
 * @see \Rector\NodeTypeResolver\Tests\PhpDoc\NodeAnalyzer\DocBlockManipulatorTest
 */
final class DocBlockManipulator
{
    /**
     * @var bool[][]
     */
    private $usedShortNameByClasses = [];

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
     * @var NodeTraverser
     */
    private $nodeTraverser;

//    /**
//     * @var FullyQualifiedObjectType[]
//     */
//    private $importedNames = [];

    /**
     * @var UseAddingCommander
     */
    private $useAddingCommander;

    /**
     * @var BetterStandardPrinter
     */
    private $betterStandardPrinter;

    /**
     * @var NameResolver
     */
    private $nameResolver;

    /**
     * @var StaticTypeMapper
     */
    private $staticTypeMapper;

    /**
     * @var bool
     */
    private $hasPhpDocChanged = false;

    public function __construct(
        PhpDocInfoFactory $phpDocInfoFactory,
        PhpDocInfoPrinter $phpDocInfoPrinter,
        AttributeAwareNodeFactory $attributeAwareNodeFactory,
        NodeTraverser $nodeTraverser,
        NameResolver $nameResolver,
        UseAddingCommander $useAddingCommander,
        BetterStandardPrinter $betterStandardPrinter,
        StaticTypeMapper $staticTypeMapper
    ) {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->phpDocInfoPrinter = $phpDocInfoPrinter;
        $this->attributeAwareNodeFactory = $attributeAwareNodeFactory;
        $this->nodeTraverser = $nodeTraverser;
        $this->useAddingCommander = $useAddingCommander;
        $this->betterStandardPrinter = $betterStandardPrinter;
        $this->nameResolver = $nameResolver;
        $this->staticTypeMapper = $staticTypeMapper;
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

        // advanced check, e.g. for "Namespaced\Annotations\DI"
        $phpDocInfo = $this->createPhpDocInfoFromNode($node);

        return (bool) $phpDocInfo->getByType($name);
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
        $this->renamePhpDocType($phpDocInfo->getPhpDocNode(), $oldType, $newType, $node);

        if ($this->hasPhpDocChanged === false) {
            return;
        }

        $this->updateNodeWithPhpDocInfo($node, $phpDocInfo);
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

        $this->removeTagFromNode($node, 'var', true);
        $this->addTypeSpecificTag($node, 'var', $newType);
    }

    public function addReturnTag(Node $node, Type $newType): void
    {
        $currentReturnType = $this->getReturnType($node);

        // make sure the tags are not identical, e.g imported class vs FQN class
        if ($this->areTypesEquals($currentReturnType, $newType)) {
            return;
        }

        $this->removeTagFromNode($node, 'return');
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

        return $this->createPhpDocInfoFromNode($node)->getVarType();
    }

    public function removeTagByName(PhpDocInfo $phpDocInfo, string $tagName): void
    {
        $phpDocNode = $phpDocInfo->getPhpDocNode();

        // A. remove class-based tag
        if (class_exists($tagName)) {
            $phpDocTagNode = $phpDocInfo->getByType($tagName);
            if ($phpDocTagNode) {
                $this->removeTagFromPhpDocNode($phpDocNode, $phpDocTagNode);
            }
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

    public function renamePhpDocType(PhpDocNode $phpDocNode, Type $oldType, Type $newType, Node $node): PhpDocNode
    {
        $phpParserNode = $node;

        $this->nodeTraverser->traverseWithCallable(
            $phpDocNode,
            function (PhpDocParserNode $node) use ($phpParserNode, $oldType, $newType): PhpDocParserNode {
                if (! $node instanceof IdentifierTypeNode) {
                    return $node;
                }

                $staticType = $this->staticTypeMapper->mapPHPStanPhpDocTypeNodeToPHPStanType($node, $phpParserNode);
                if ($staticType->equals($oldType)) {
                    $newIdentifierType = $this->staticTypeMapper->mapPHPStanTypeToPHPStanPhpDocTypeNode($newType);

                    if ($newIdentifierType === null) {
                        throw new ShouldNotHappenException();
                    }

                    if ($newIdentifierType instanceof IdentifierTypeNode) {
                        throw new ShouldNotHappenException();
                    }

                    //                    $node->name = $this->staticTypeMapper->mapPHPStanTypeToPHPStanPhpDocTypeNode($newType);
                    $this->hasPhpDocChanged = true;
                    return $newIdentifierType;
                }

                return $node;
            }
        );

        return $phpDocNode;
    }

    /**
     * @return FullyQualifiedObjectType[]
     */
    public function importNames(Node $node): array
    {
        if ($node->getDocComment() === null) {
            return [];
        }

        $phpDocInfo = $this->createPhpDocInfoFromNode($node);
        $phpDocNode = $phpDocInfo->getPhpDocNode();
        $phpParserNode = $node;

        $this->nodeTraverser->traverseWithCallable($phpDocNode, function (PhpDocParserNode $docNode) use (
            $node,
            $phpParserNode
        ): PhpDocParserNode {
            if (! $docNode instanceof IdentifierTypeNode) {
                return $docNode;
            }

            $staticType = $this->staticTypeMapper->mapPHPStanPhpDocTypeNodeToPHPStanType($docNode, $phpParserNode);

            // already imported
            if (! $staticType instanceof FullyQualifiedObjectType) {
                return $docNode;
            }

            return $this->processFqnNameImport($node, $docNode, $staticType);
        });

        if ($this->hasPhpDocChanged) {
            $this->updateNodeWithPhpDocInfo($node, $phpDocInfo);
        }

        return [];
//        return $this->importedNames;
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

        $this->nodeTraverser->traverseWithCallable($phpDocNode, function (PhpDocParserNode $node) use (
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

        if ($this->hasPhpDocChanged === false) {
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

        return (bool) Strings::match($docComment->getText(), '#\@(param|throws|return|var)\b#');
    }

    public function updateNodeWithPhpDocInfo(
        Node $node,
        PhpDocInfo $phpDocInfo,
        bool $shouldSkipEmptyLinesAbove = false
    ): bool {
        // skip if has no doc comment
        if ($node->getDocComment() === null) {
            return false;
        }

        $phpDoc = $this->phpDocInfoPrinter->printFormatPreserving($phpDocInfo, $shouldSkipEmptyLinesAbove);
        if ($phpDoc !== '') {
            // no change, don't save it
            if ($node->getDocComment()->getText() === $phpDoc) {
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
        $paramTypes = $this->getParamTypesByName($functionLike);
        return $paramTypes[$paramName] ?? new MixedType();
    }

    /**
     * All class-type tags are FQN by default to keep default convention through the code.
     * Some people prefer FQN, some short. FQN can be shorten with \Rector\CodingStyle\Rector\Namespace_\ImportFullyQualifiedNamesRector later, while short prolonged not
     */
    private function addTypeSpecificTag(Node $node, string $name, Type $type): void
    {
        $docStringType = $this->staticTypeMapper->mapPHPStanTypeToDocString($type);

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

    private function processFqnNameImport(
        Node $node,
        IdentifierTypeNode $identifierTypeNode,
        FullyQualifiedObjectType $fullyQualifiedObjectType
    ): PhpDocParserNode {
        // nothing to be changed → skip
        if ($this->hasTheSameShortClassInCurrentNamespace($node, $fullyQualifiedObjectType)) {
            return $identifierTypeNode;
        }

        if ($this->useAddingCommander->isShortImported($node, $fullyQualifiedObjectType)) {
            if ($this->useAddingCommander->isImportShortable($node, $fullyQualifiedObjectType)) {
                $identifierTypeNode->name = $fullyQualifiedObjectType->getShortName();
                $this->hasPhpDocChanged = true;
            }

            return $identifierTypeNode;
        }

        $identifierTypeNode->name = $fullyQualifiedObjectType->getShortName();
        $this->hasPhpDocChanged = true;
        $this->useAddingCommander->addUseImport($node, $fullyQualifiedObjectType);

        return $identifierTypeNode;
    }

    private function isCurrentNamespaceSameShortClassAlreadyUsed(
        Node $node,
        string $fullyQualifiedName,
        ShortenedObjectType $shortenedObjectType
    ): bool {
        /** @var ClassLike|null $classNode */
        $classNode = $node->getAttribute(AttributeKey::CLASS_NODE);
        if ($classNode === null) {
            // cannot say, so rather yes
            return true;
        }

        $className = $this->nameResolver->getName($classNode);

        if (isset($this->usedShortNameByClasses[$className][$shortenedObjectType->getShortName()])) {
            return $this->usedShortNameByClasses[$className][$shortenedObjectType->getShortName()];
        }

        $printedClass = $this->betterStandardPrinter->print($classNode->stmts);

        // short with space " Type"| fqn
        $shortNameOrFullyQualifiedNamePattern = sprintf(
            '#(\s%s\b|\b%s\b)#',
            preg_quote($shortenedObjectType->getShortName()),
            preg_quote($fullyQualifiedName)
        );

        $isShortClassUsed = (bool) Strings::match($printedClass, $shortNameOrFullyQualifiedNamePattern);

        $this->usedShortNameByClasses[$className][$shortenedObjectType->getShortName()] = $isShortClassUsed;

        return $isShortClassUsed;
    }

    private function areTypesEquals(Type $firstType, Type $secondType): bool
    {
        return $this->staticTypeMapper->createTypeHash($firstType) === $this->staticTypeMapper->createTypeHash(
            $secondType
        );
    }

    /**
     * The class in the same namespace as different file can se used in this code, the short names would colide → skip
     *
     * E.g. this namespace:
     * App\Product
     *
     * And the FQN:
     * App\SomeNesting\Product
     */
    private function hasTheSameShortClassInCurrentNamespace(
        Node $node,
        FullyQualifiedObjectType $fullyQualifiedObjectType
    ): bool {
        // the name is already in the same namespace implicitly
        $namespaceName = $node->getAttribute(AttributeKey::NAMESPACE_NAME);
        $currentNamespaceShortName = $namespaceName . '\\' . $fullyQualifiedObjectType->getShortName();

        if ($this->doesClassLikeExist($currentNamespaceShortName)) {
            return false;
        }

        if ($currentNamespaceShortName === $fullyQualifiedObjectType->getClassName()) {
            return false;
        }

        return $this->isCurrentNamespaceSameShortClassAlreadyUsed(
            $node,
            $currentNamespaceShortName,
            $fullyQualifiedObjectType->getShortNameType()
        );
    }

    private function doesClassLikeExist(string $classLike): bool
    {
        return class_exists($classLike) || interface_exists($classLike) || trait_exists($classLike);
    }
}
