<?php

declare (strict_types=1);
namespace Rector\Core\ProcessAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Stmt;
use Rector\Core\Contract\Rector\RectorInterface;
use Rector\Core\ValueObject\Application\File;
use Rector\Core\ValueObject\RectifiedNode;
use Rector\NodeTypeResolver\Node\AttributeKey;
/**
 * This service verify if the Node already rectified with same Rector rule before current Rector rule with condition
 *
 *        Same Rector Rule <-> Same Node <-> Same File
 */
final class RectifiedAnalyzer
{
    /**
     * @var array<string, RectifiedNode|null>
     */
    private $previousFileWithNodes = [];
    public function verify(\Rector\Core\Contract\Rector\RectorInterface $rector, \PhpParser\Node $node, \Rector\Core\ValueObject\Application\File $currentFile) : ?\Rector\Core\ValueObject\RectifiedNode
    {
        $smartFileInfo = $currentFile->getSmartFileInfo();
        $realPath = $smartFileInfo->getRealPath();
        if (!isset($this->previousFileWithNodes[$realPath])) {
            $this->previousFileWithNodes[$realPath] = new \Rector\Core\ValueObject\RectifiedNode(\get_class($rector), $node);
            return null;
        }
        /** @var RectifiedNode $rectifiedNode */
        $rectifiedNode = $this->previousFileWithNodes[$realPath];
        if ($this->shouldContinue($rectifiedNode, $rector, $node)) {
            return null;
        }
        // re-set to refill next
        $this->previousFileWithNodes[$realPath] = null;
        return $rectifiedNode;
    }
    private function shouldContinue(\Rector\Core\ValueObject\RectifiedNode $rectifiedNode, \Rector\Core\Contract\Rector\RectorInterface $rector, \PhpParser\Node $node) : bool
    {
        if ($rectifiedNode->getRectorClass() === \get_class($rector) && $rectifiedNode->getNode() === $node) {
            return \false;
        }
        if ($node instanceof \PhpParser\Node\Stmt) {
            return \true;
        }
        $stmt = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CURRENT_STATEMENT);
        if ($stmt instanceof \PhpParser\Node\Stmt) {
            return \true;
        }
        $startTokenPos = $node->getStartTokenPos();
        $endTokenPos = $node->getEndTokenPos();
        return $startTokenPos < 0 || $endTokenPos < 0;
    }
}
