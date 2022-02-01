<?php

declare(strict_types=1);

namespace Rector\Core\ProcessAnalyzer;

use PhpParser\Node;
use Rector\Core\Contract\Rector\RectorInterface;
use Rector\Core\ValueObject\Application\File;
use Rector\Core\ValueObject\RectifiedNode;
use Rector\NodeNameResolver\NodeNameResolver;
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
    private array $previousFileWithNodes = [];

    public function __construct(
        private readonly NodeNameResolver $nodeNameResolver
    ) {
    }

    public function verify(RectorInterface $rector, Node $node, ?Node $originalNode, File $currentFile): ?RectifiedNode
    {
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

        $createdByRule = $node->getAttribute(AttributeKey::CREATED_BY_RULE) ?? [];
        $nodeName = $this->nodeNameResolver->getName($node);
        $originalNodeName = $originalNode instanceof Node
            ? $this->nodeNameResolver->getName($originalNode)
            : null;

        if (is_string($nodeName) && $nodeName === $originalNodeName && $createdByRule !== [] && ! in_array(
            $rector::class,
            $createdByRule,
            true
        )) {
            return null;
        }

        // re-set to refill next
        $this->previousFileWithNodes[$realPath] = null;
        return $rectifiedNode;
    }
}
