<?php

declare(strict_types=1);

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
    private const EXCLUDE_NODES = [Assign::class, Class_::class];

    /**
     * @var array<string, RectifiedNode|null>
     */
    private array $previousFileWithNodes = [];

    public function __construct(
        private readonly PhpDocInfoFactory $phpDocInfoFactory
    ) {
    }

    public function verify(RectorInterface $rector, Node $node, File $currentFile): ?RectifiedNode
    {
        if (in_array($node::class, self::EXCLUDE_NODES, true)) {
            return null;
        }

        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        if ($phpDocInfo->hasChanged()) {
            return null;
        }

        $smartFileInfo = $currentFile->getSmartFileInfo();
        $realPath = $smartFileInfo->getRealPath();

        if (! isset($this->previousFileWithNodes[$realPath])) {
            $this->previousFileWithNodes[$realPath] = new RectifiedNode($rector::class, $node);
            return null;
        }

        /** @var RectifiedNode $rectifiedNode */
        $rectifiedNode = $this->previousFileWithNodes[$realPath];
        if ($rectifiedNode->getRectorClass() !== $rector::class) {
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
