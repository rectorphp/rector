<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Comments\NodeDocBlock;

use RectorPrefix20220606\PhpParser\Comment\Doc;
use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use RectorPrefix20220606\Rector\BetterPhpDocParser\Printer\PhpDocInfoPrinter;
use RectorPrefix20220606\Rector\NodeTypeResolver\Node\AttributeKey;
final class DocBlockUpdater
{
    /**
     * @var string
     * @see https://regex101.com/r/VdaVGL/1
     */
    public const SPACE_OR_ASTERISK_REGEX = '#(\\s|\\*)+#';
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\Printer\PhpDocInfoPrinter
     */
    private $phpDocInfoPrinter;
    public function __construct(PhpDocInfoPrinter $phpDocInfoPrinter)
    {
        $this->phpDocInfoPrinter = $phpDocInfoPrinter;
    }
    public function updateNodeWithPhpDocInfo(Node $node) : void
    {
        // nothing to change? don't save it
        $phpDocInfo = $node->getAttribute(AttributeKey::PHP_DOC_INFO);
        if (!$phpDocInfo instanceof PhpDocInfo) {
            return;
        }
        if (!$phpDocInfo->hasChanged()) {
            return;
        }
        $phpDoc = $this->printPhpDocInfoToString($phpDocInfo);
        // make sure, that many separated comments are not removed
        if ($phpDoc === '') {
            if (\count($node->getComments()) > 1) {
                foreach ($node->getComments() as $comment) {
                    $phpDoc .= $comment->getText() . \PHP_EOL;
                }
            }
            if ($phpDocInfo->getOriginalPhpDocNode()->children !== []) {
                // all comments were removed → null
                $node->setAttribute(AttributeKey::COMMENTS, null);
            }
            return;
        }
        // this is needed to remove duplicated // commentsAsText
        $node->setDocComment(new Doc($phpDoc));
    }
    private function printPhpDocInfoToString(PhpDocInfo $phpDocInfo) : string
    {
        if ($phpDocInfo->isNewNode()) {
            return $this->phpDocInfoPrinter->printNew($phpDocInfo);
        }
        return $this->phpDocInfoPrinter->printFormatPreserving($phpDocInfo);
    }
}
