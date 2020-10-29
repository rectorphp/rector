<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer;

use Nette\Utils\Strings;
use PhpParser\Comment\Doc;
use PhpParser\Node;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\Annotation\StaticAnnotationNaming;
use Rector\BetterPhpDocParser\Contract\Doctrine\DoctrineRelationTagValueNodeInterface;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\TypeAwareTagValueNodeInterface;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\Printer\PhpDocInfoPrinter;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Renaming\ValueObject\RenameAnnotation;

final class DocBlockManipulator
{
    /**
     * @var string
     * @see https://regex101.com/r/VdaVGL/1
     */
    public const SPACE_OR_ASTERISK_REGEX = '#(\s|\*)+#';

    /**
     * @var string
     * @see https://regex101.com/r/Mjb0qi/1
     */
    private const NEWLINE_CLOSING_DOC_REGEX = "#\n \*\/$#";

    /**
     * @var string
     * @see https://regex101.com/r/U5OUV4/2
     */
    private const NEWLINE_MIDDLE_DOC_REGEX = "#\n \* #";

    /**
     * @var PhpDocInfoPrinter
     */
    private $phpDocInfoPrinter;

    /**
     * @var DocBlockClassRenamer
     */
    private $docBlockClassRenamer;

    public function __construct(DocBlockClassRenamer $docBlockClassRenamer, PhpDocInfoPrinter $phpDocInfoPrinter)
    {
        $this->phpDocInfoPrinter = $phpDocInfoPrinter;
        $this->docBlockClassRenamer = $docBlockClassRenamer;
    }

    public function changeType(Node $node, Type $oldType, Type $newType): void
    {
        /** @var PhpDocInfo $phpDocInfo */
        $phpDocInfo = $node->getAttribute(AttributeKey::PHP_DOC_INFO);

        $this->docBlockClassRenamer->renamePhpDocType($phpDocInfo->getPhpDocNode(), $oldType, $newType, $node);
    }

    public function replaceAnnotationInNode(Node $node, RenameAnnotation $renameAnnotation): void
    {
        /** @var PhpDocInfo $phpDocInfo */
        $phpDocInfo = $node->getAttribute(AttributeKey::PHP_DOC_INFO);

        $this->replaceTagByAnother(
            $phpDocInfo->getPhpDocNode(),
            $renameAnnotation->getOldAnnotation(),
            $renameAnnotation->getNewAnnotation()
        );
    }

    public function replaceTagByAnother(PhpDocNode $phpDocNode, string $oldTag, string $newTag): void
    {
        $oldTag = StaticAnnotationNaming::normalizeName($oldTag);
        $newTag = StaticAnnotationNaming::normalizeName($newTag);

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
     * For better performance
     */
    public function hasNodeTypeTags(Node $node): bool
    {
        /** @var PhpDocInfo|null $phpDocInfo */
        $phpDocInfo = $node->getAttribute(AttributeKey::PHP_DOC_INFO);
        if ($phpDocInfo === null) {
            return false;
        }

        return $phpDocInfo->hasByType(TypeAwareTagValueNodeInterface::class);
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

        // make sure, that many separated comments are not removed
        if ($phpDoc === '' && count($node->getComments()) > 1) {
            foreach ($node->getComments() as $comment) {
                $phpDoc .= $comment->getText() . PHP_EOL;
            }
        }

        if ($phpDoc === '') {
            if ($phpDocInfo->getOriginalPhpDocNode()->children !== []) {
                // all comments were removed → null
                $node->setAttribute(AttributeKey::COMMENTS, null);
            }

            return;
        }

        // no change, don't save it
        // this is needed to prevent short classes override with FQN with same value → people don't like that for some reason
        if (! $this->haveDocCommentOrCommentsChanged($node, $phpDoc)) {
            return;
        }

        // this is needed to remove duplicated // commentsAsText
        $node->setDocComment(new Doc($phpDoc));
    }

    public function getDoctrineFqnTargetEntity(Node $node): ?string
    {
        /** @var PhpDocInfo $phpDocInfo */
        $phpDocInfo = $node->getAttribute(AttributeKey::PHP_DOC_INFO);

        $doctrineRelationTagValueNode = $phpDocInfo->getByType(DoctrineRelationTagValueNodeInterface::class);
        if ($doctrineRelationTagValueNode === null) {
            return null;
        }

        return $doctrineRelationTagValueNode->getFullyQualifiedTargetEntity();
    }

    private function printPhpDocInfoToString(PhpDocInfo $phpDocInfo): string
    {
        // new node, needs to be reparsed
        if ($phpDocInfo->isNewNode()) {
            $docContent = (string) $phpDocInfo->getPhpDocNode();
            if (! $phpDocInfo->isSingleLine()) {
                return $docContent;
            }

            return $this->inlineDocContent($docContent);
        }

        return $this->phpDocInfoPrinter->printFormatPreserving($phpDocInfo);
    }

    private function haveDocCommentOrCommentsChanged(Node $node, string $phpDoc): bool
    {
        $docComment = $node->getDocComment();
        if ($docComment !== null && $docComment->getText() === $phpDoc) {
            return false;
        }

        $phpDoc = $this->completeSimpleCommentsToPhpDoc($node, $phpDoc);

        if ($node->getComments() !== []) {
            $commentsContent = implode(PHP_EOL, $node->getComments());

            if ($this->removeSpacesAndAsterisks($commentsContent) === $this->removeSpacesAndAsterisks($phpDoc)) {
                return false;
            }
        }

        return true;
    }

    private function inlineDocContent(string $docContent): string
    {
        $docContent = Strings::replace($docContent, self::NEWLINE_MIDDLE_DOC_REGEX, ' ');

        return Strings::replace($docContent, self::NEWLINE_CLOSING_DOC_REGEX, ' */');
    }

    /**
     * add // comments to phpdoc (only has /**
     */
    private function completeSimpleCommentsToPhpDoc(Node $node, string $phpDoc): string
    {
        $startComments = '';
        foreach ($node->getComments() as $comment) {
            // skip non-simple comments
            if (! Strings::startsWith($comment->getText(), '//')) {
                continue;
            }

            $startComments .= $comment->getText();
        }

        if ($startComments === '') {
            return $phpDoc;
        }

        return $startComments . PHP_EOL . $phpDoc;
    }

    private function removeSpacesAndAsterisks(string $content): string
    {
        return Strings::replace($content, self::SPACE_OR_ASTERISK_REGEX);
    }
}
