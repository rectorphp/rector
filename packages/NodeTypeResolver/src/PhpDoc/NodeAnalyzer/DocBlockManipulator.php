<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer;

use Nette\Utils\Strings;
use PhpParser\Comment\Doc;
use PhpParser\Node;
use PHPStan\PhpDocParser\Ast\Node as PhpDocParserNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocChildNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\ThrowsTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\Annotation\AnnotationNaming;
use Rector\BetterPhpDocParser\Ast\PhpDocNodeTraverser;
use Rector\BetterPhpDocParser\Attributes\Ast\AttributeAwareNodeFactory;
use Rector\BetterPhpDocParser\Contract\Doctrine\DoctrineRelationTagValueNodeInterface;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\TypeAwareTagValueNodeInterface;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\Printer\PhpDocInfoPrinter;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\StaticTypeMapper;

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
     * @var DocBlockClassRenamer
     */
    private $docBlockClassRenamer;

    public function __construct(
        PhpDocInfoPrinter $phpDocInfoPrinter,
        AttributeAwareNodeFactory $attributeAwareNodeFactory,
        PhpDocNodeTraverser $phpDocNodeTraverser,
        StaticTypeMapper $staticTypeMapper,
        DocBlockClassRenamer $docBlockClassRenamer
    ) {
        $this->phpDocInfoPrinter = $phpDocInfoPrinter;
        $this->attributeAwareNodeFactory = $attributeAwareNodeFactory;
        $this->phpDocNodeTraverser = $phpDocNodeTraverser;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->docBlockClassRenamer = $docBlockClassRenamer;
    }

    /**
     * @deprecated
     * Use
     * @see PhpDocInfo::addBareTag()
     * @see PhpDocInfo::addPhpDocTagNode()
     * @see PhpDocInfo::addTagValueNode()
     */
    public function addTag(Node $node, PhpDocChildNode $phpDocChildNode): void
    {
        $phpDocChildNode = $this->attributeAwareNodeFactory->createFromNode($phpDocChildNode);

        /** @var PhpDocInfo $phpDocInfo */
        $phpDocInfo = $node->getAttribute(AttributeKey::PHP_DOC_INFO);
        $phpDocInfo->addPhpDocTagNode($phpDocChildNode);
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
        /** @var PhpDocInfo $phpDocInfo */
        $phpDocInfo = $node->getAttribute(AttributeKey::PHP_DOC_INFO);

        $this->replaceTagByAnother($phpDocInfo->getPhpDocNode(), $oldAnnotation, $newAnnotation);
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

            return $node;
        });
    }

    /**
     * For better performance
     */
    public function hasNodeTypeTags(Node $node): bool
    {
        /** @var PhpDocInfo|null $phpDocInfo */
        $phpDocInfo = $node->getAttribute(AttributeKey::PHP_DOC_INFO);
        if ($phpDocInfo === null) {
            return false;
        }

        $tagValueNodesWithType = [
            VarTagValueNode::class,
            ThrowsTagValueNode::class,
            ReturnTagValueNode::class,
            VarTagValueNode::class,
            TypeAwareTagValueNodeInterface::class,
        ];

        foreach ($tagValueNodesWithType as $tagValueNodeWithType) {
            if ($phpDocInfo->hasByType($tagValueNodeWithType)) {
                return true;
            }
        }

        return false;
    }

    public function updateNodeWithPhpDocInfo(Node $node): void
    {
        // nothing to change
        /** @var PhpDocInfo|null $phpDocInfo */
        $phpDocInfo = $node->getAttribute(AttributeKey::PHP_DOC_INFO);
        if ($phpDocInfo === null) {
            return;
        }

        $phpDoc = $this->printPhpDocInfoToString($phpDocInfo);
        if ($phpDoc === '') {
            // no comments, null
            $node->setAttribute('comments', null);
            return;
        }

        // no change, don't save it
        // this is needed to prevent short classes override with FQN with same value → people don't like that for some reason
        if (! $this->haveDocCommentOrCommentsChanged($node, $phpDoc)) {
            return;
        }

        // this is needed to remove duplicated // comments
        $node->setAttribute('comments', null);
        $node->setDocComment(new Doc($phpDoc));
    }

    public function getDoctrineFqnTargetEntity(Node $node): ?string
    {
        /** @var PhpDocInfo $phpDocInfo */
        $phpDocInfo = $node->getAttribute(AttributeKey::PHP_DOC_INFO);

        $relationTagValueNode = $phpDocInfo->getByType(DoctrineRelationTagValueNodeInterface::class);
        if ($relationTagValueNode === null) {
            return null;
        }

        return $relationTagValueNode->getFqnTargetEntity();
    }

    private function printPhpDocInfoToString(PhpDocInfo $phpDocInfo): string
    {
        // new node, needs to be reparsed
        if ($phpDocInfo->getPhpDocNode()->children !== [] && $phpDocInfo->getTokens() === []) {
            return (string) $phpDocInfo->getPhpDocNode();
        }

        return $this->phpDocInfoPrinter->printFormatPreserving($phpDocInfo);
    }

    private function haveDocCommentOrCommentsChanged(Node $node, string $phpDoc): bool
    {
        $docComment = $node->getDocComment();
        if ($docComment !== null && $docComment->getText() === $phpDoc) {
            return false;
        }

        if ($node->getComments() !== []) {
            $commentsContent = implode('', $node->getComments());
            if ($commentsContent === $phpDoc) {
                return false;
            }
        }

        return true;
    }
}
