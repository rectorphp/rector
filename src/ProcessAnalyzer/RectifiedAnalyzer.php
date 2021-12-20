<?php

declare (strict_types=1);
namespace Rector\Core\ProcessAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Stmt\Class_;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Core\Contract\Rector\RectorInterface;
use Rector\Core\ValueObject\Application\File;
use Rector\Core\ValueObject\RectifiedNode;
/**
 * This service verify if the Node already rectified with same Rector rule before current Rector rule with condition
 *
 *        Same Rector Rule <-> Same Node <-> Same File
 *
 * Some limitations:
 *
 *   - only check against Node which not Assign or Class_
 *   - The checked node doesn't has PhpDocInfo changed.
 *
 * which possibly changed by other process.
 */
final class RectifiedAnalyzer
{
    /**
     * @var array<class-string<Node>>
     */
    private const EXCLUDE_NODES = [\PhpParser\Node\Expr\Assign::class, \PhpParser\Node\Stmt\Class_::class];
    /**
     * @var array<string, RectifiedNode|null>
     */
    private $previousFileWithNodes = [];
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    public function __construct(\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory $phpDocInfoFactory)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
    }
    public function verify(\Rector\Core\Contract\Rector\RectorInterface $rector, \PhpParser\Node $node, \Rector\Core\ValueObject\Application\File $currentFile) : ?\Rector\Core\ValueObject\RectifiedNode
    {
        if (\in_array(\get_class($node), self::EXCLUDE_NODES, \true)) {
            return null;
        }
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        if ($phpDocInfo->hasChanged()) {
            return null;
        }
        $smartFileInfo = $currentFile->getSmartFileInfo();
        $realPath = $smartFileInfo->getRealPath();
        if (!isset($this->previousFileWithNodes[$realPath])) {
            $this->previousFileWithNodes[$realPath] = new \Rector\Core\ValueObject\RectifiedNode(\get_class($rector), $node);
            return null;
        }
        /** @var RectifiedNode $rectifiedNode */
        $rectifiedNode = $this->previousFileWithNodes[$realPath];
        if ($rectifiedNode->getRectorClass() !== \get_class($rector)) {
            return null;
        }
        if ($rectifiedNode->getNode() !== $node) {
            return null;
        }
        // re-set to refill next
        $this->previousFileWithNodes[$realPath] = null;
        return $rectifiedNode;
    }
}
