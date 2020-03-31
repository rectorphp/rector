<?php

declare(strict_types=1);

namespace Rector\Core\Application;

use PhpParser\Node;
use Rector\Core\Contract\PhpParser\Node\CommanderInterface;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\FileSystem\CurrentFileInfoProvider;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\SmartFileSystem\SmartFileInfo;

final class PostFileProcessor
{
    /**
     * @var CommanderInterface[]
     */
    private $commanders = [];

    /**
     * @var CurrentFileInfoProvider
     */
    private $currentFileInfoProvider;

    /**
     * @param CommanderInterface[] $commanders
     */
    public function __construct(CurrentFileInfoProvider $currentFileInfoProvider, array $commanders)
    {
        $this->sortByPriorityAndSetCommanders($commanders);
        $this->currentFileInfoProvider = $currentFileInfoProvider;
    }

    /**
     * @param Node[] $nodes
     * @return Node[]
     */
    public function traverseNodes(array $nodes): array
    {
        $this->setCurrentFileInfo($nodes);

        foreach ($this->commanders as $commander) {
            if (! $commander->isActive()) {
                continue;
            }

            $nodes = $commander->traverseNodes($nodes);
        }

        return $nodes;
    }

    /**
     * @param CommanderInterface[] $commanders
     */
    private function sortByPriorityAndSetCommanders(array $commanders): void
    {
        $commandersByPriority = [];

        foreach ($commanders as $commander) {
            if (isset($commandersByPriority[$commander->getPriority()])) {
                throw new ShouldNotHappenException();
            }

            $commandersByPriority[$commander->getPriority()] = $commander;
        }

        krsort($commandersByPriority);

        $this->commanders = $commandersByPriority;
    }

    /**
     * @param Node[] $nodes
     */
    private function setCurrentFileInfo(array $nodes): void
    {
        foreach ($nodes as $node) {
            /** @var SmartFileInfo|null $fileInfo */
            $fileInfo = $node->getAttribute(AttributeKey::FILE_INFO);
            if (! $fileInfo instanceof SmartFileInfo) {
                continue;
            }

            $this->currentFileInfoProvider->setCurrentFileInfo($fileInfo);
            break;
        }
    }
}
