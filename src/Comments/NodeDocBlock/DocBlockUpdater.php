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
     * @var \Rector\BetterPhpDocParser\Printer\PhpDocInfoPrinter
     */
    private $phpDocInfoPrinter;
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
    }
    private function setCommentsAttribute(Node $node) : void
    {
        $comments = \array_filter($node->getComments(), static function (Comment $comment) : bool {
            return !$comment instanceof Doc;
        });
        $node->setAttribute(AttributeKey::COMMENTS, $comments);
    }
    private function printPhpDocInfoToString(PhpDocInfo $phpDocInfo) : string
    {
        if ($phpDocInfo->isNewNode()) {
            return $this->phpDocInfoPrinter->printNew($phpDocInfo);
        }
        return $this->phpDocInfoPrinter->printFormatPreserving($phpDocInfo);
    }
}
