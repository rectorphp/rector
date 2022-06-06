<?php

declare (strict_types=1);
namespace Rector\Core\ProcessAnalyzer;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use Rector\Core\Contract\Rector\RectorInterface;
use Rector\Core\PhpParser\Comparing\NodeComparator;
use Rector\Core\Rector\AbstractScopeAwareRector;
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
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Comparing\NodeComparator
     */
    private $nodeComparator;
    public function __construct(\Rector\Core\PhpParser\Comparing\NodeComparator $nodeComparator)
    {
        $this->nodeComparator = $nodeComparator;
    }
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
        $originalNode = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::ORIGINAL_NODE);
        if ($rectifiedNode->getRectorClass() === \get_class($rector) && $rectifiedNode->getNode() === $node) {
            /**
             * allow to revisit the Node with same Rector rule if Node is changed by other rule
             */
            return !$this->nodeComparator->areNodesEqual($originalNode, $node);
        }
        if ($rector instanceof \Rector\Core\Rector\AbstractScopeAwareRector) {
            $scope = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
            return $scope instanceof \PHPStan\Analyser\Scope;
        }
        if ($originalNode instanceof \PhpParser\Node) {
            return \true;
        }
        $startTokenPos = $node->getStartTokenPos();
        return $startTokenPos < 0;
    }
}
