<?php

declare (strict_types=1);
namespace Rector\BetterPhpDocParser\Comment;

use PhpParser\Node;
use PhpParser\Node\Stmt\InlineHTML;
use PhpParser\Node\Stmt\Nop;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpParser\Comparing\NodeComparator;
final class CommentsMerger
{
    /**
     * @readonly
     */
    private NodeComparator $nodeComparator;
    public function __construct(NodeComparator $nodeComparator)
    {
        $this->nodeComparator = $nodeComparator;
    }
    public function mirrorComments(Node $newNode, Node $oldNode): void
    {
        if ($oldNode instanceof InlineHTML) {
            return;
        }
        if ($this->nodeComparator->areSameNode($newNode, $oldNode)) {
            return;
        }
        $oldPhpDocInfo = $oldNode->getAttribute(AttributeKey::PHP_DOC_INFO);
        $newPhpDocInfo = $newNode->getAttribute(AttributeKey::PHP_DOC_INFO);
        if ($newPhpDocInfo instanceof PhpDocInfo) {
            if (!$oldPhpDocInfo instanceof PhpDocInfo) {
                return;
            }
            if ((string) $oldPhpDocInfo->getPhpDocNode() !== (string) $newPhpDocInfo->getPhpDocNode()) {
                return;
            }
        }
        $newNode->setAttribute(AttributeKey::PHP_DOC_INFO, $oldPhpDocInfo);
        if (!$newNode instanceof Nop) {
            $newNode->setAttribute(AttributeKey::COMMENTS, $oldNode->getAttribute(AttributeKey::COMMENTS));
        }
    }
    /**
     * @param Node[] $mergedNodes
     */
    public function keepComments(Node $newNode, array $mergedNodes): void
    {
        $comments = $newNode->getComments();
        foreach ($mergedNodes as $mergedNode) {
            $comments = array_merge($comments, $mergedNode->getComments());
        }
        if ($comments === []) {
            return;
        }
        $newNode->setAttribute(AttributeKey::COMMENTS, $comments);
        // remove so comments "win"
        $newNode->setAttribute(AttributeKey::PHP_DOC_INFO, null);
    }
}
