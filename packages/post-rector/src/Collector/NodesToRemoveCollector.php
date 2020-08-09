<?php

declare(strict_types=1);

namespace Rector\PostRector\Collector;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Expression;
use Rector\ChangesReporting\Collector\AffectedFilesCollector;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\NodeRemoval\BreakingRemovalGuard;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PostRector\Contract\Collector\NodeCollectorInterface;
use Symplify\SmartFileSystem\SmartFileInfo;

final class NodesToRemoveCollector implements NodeCollectorInterface
{
    /**
     * @var AffectedFilesCollector
     */
    private $affectedFilesCollector;

    /**
     * @var BreakingRemovalGuard
     */
    private $breakingRemovalGuard;

    /**
     * @var Stmt[]|Node[]
     */
    private $nodesToRemove = [];

    public function __construct(
        AffectedFilesCollector $affectedFilesCollector,
        BreakingRemovalGuard $breakingRemovalGuard
    ) {
        $this->affectedFilesCollector = $affectedFilesCollector;
        $this->breakingRemovalGuard = $breakingRemovalGuard;
    }

    public function addNodeToRemove(Node $node): void
    {
        // chain call: "->method()->another()"
        $this->ensureIsNotPartOfChainMethodCall($node);

        $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
        if (! $node instanceof Expression && $parentNode instanceof Expression) {
            // only expressions can be removed
            $node = $parentNode;
        } else {
            $this->breakingRemovalGuard->ensureNodeCanBeRemove($node);
        }

        /** @var SmartFileInfo|null $fileInfo */
        $fileInfo = $node->getAttribute(AttributeKey::FILE_INFO);
        if ($fileInfo !== null) {
            $this->affectedFilesCollector->addFile($fileInfo);
        }

        /** @var Stmt $node */
        $this->nodesToRemove[] = $node;
    }

    public function isNodeRemoved(Node $node): bool
    {
        return in_array($node, $this->nodesToRemove, true);
    }

    public function isActive(): bool
    {
        return $this->getCount() > 0;
    }

    public function getCount(): int
    {
        return count($this->nodesToRemove);
    }

    /**
     * @return Node[]
     */
    public function getNodesToRemove(): array
    {
        return $this->nodesToRemove;
    }

    public function unset(int $key): void
    {
        unset($this->nodesToRemove[$key]);
    }

    private function ensureIsNotPartOfChainMethodCall(Node $node): void
    {
        if (! $node instanceof MethodCall) {
            return;
        }

        if (! $node->var instanceof MethodCall) {
            return;
        }

        throw new ShouldNotHappenException(
            'Chain method calls cannot be removed this way. It would remove the whole tree of calls. Remove them manually by creating new parent node with no following method.'
        );
    }
}
