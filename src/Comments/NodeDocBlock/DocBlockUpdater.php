<?php

declare (strict_types=1);
namespace Rector\Comments\NodeDocBlock;

use PhpParser\Comment;
use PhpParser\Comment\Doc;
use PhpParser\Node;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\Printer\PhpDocInfoPrinter;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class DocBlockUpdater
{
    /**
     * @readonly
     */
    private PhpDocInfoPrinter $phpDocInfoPrinter;
    public function __construct(PhpDocInfoPrinter $phpDocInfoPrinter)
    {
        $this->phpDocInfoPrinter = $phpDocInfoPrinter;
    }
    public function updateRefactoredNodeWithPhpDocInfo(Node $node) : void
    {
        // nothing to change? don't save it
        $phpDocInfo = $node->getAttribute(AttributeKey::PHP_DOC_INFO);
        if (!$phpDocInfo instanceof PhpDocInfo) {
            return;
        }
        $phpDocNode = $phpDocInfo->getPhpDocNode();
        if ($phpDocNode->children === []) {
            $this->setCommentsAttribute($node);
            return;
        }
        $printedPhpDoc = $this->printPhpDocInfoToString($phpDocInfo);
        $node->setDocComment(new Doc($printedPhpDoc));
        if ($printedPhpDoc === '') {
            $this->clearEmptyDoc($node);
        }
    }
    private function setCommentsAttribute(Node $node) : void
    {
        $comments = \array_filter($node->getComments(), static fn(Comment $comment): bool => !$comment instanceof Doc);
        $node->setAttribute(AttributeKey::COMMENTS, \array_values($comments));
    }
    private function clearEmptyDoc(Node $node) : void
    {
        $comments = \array_filter($node->getComments(), static fn(Comment $comment): bool => !$comment instanceof Doc || $comment->getText() !== '');
        $node->setAttribute(AttributeKey::COMMENTS, \array_values($comments));
    }
    private function printPhpDocInfoToString(PhpDocInfo $phpDocInfo) : string
    {
        if ($phpDocInfo->isNewNode()) {
            return $this->phpDocInfoPrinter->printNew($phpDocInfo);
        }
        return $this->phpDocInfoPrinter->printFormatPreserving($phpDocInfo);
    }
}
