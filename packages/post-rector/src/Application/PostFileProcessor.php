<?php

declare(strict_types=1);

namespace Rector\PostRector\Application;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use Rector\Core\Contract\PhpParser\Node\CommanderInterface;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Tests\Rector\Architecture\DoctrineRepositoryAsService\Source\Entity\Post;
use Rector\NodeTypeResolver\FileSystem\CurrentFileInfoProvider;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PostRector\Contract\Rector\PostRectorInterface;
use Symplify\SmartFileSystem\SmartFileInfo;

final class PostFileProcessor extends NodeTraverser
{
    /**
     * @var CommanderInterface[]|PostRectorInterface[]
     */
    private $commanders = [];

    /**
     * @var CurrentFileInfoProvider
     */
    private $currentFileInfoProvider;

    /**
     * @param CommanderInterface[] $commanders
     * @param PostRectorInterface[] $postRectors
     */
    public function __construct(CurrentFileInfoProvider $currentFileInfoProvider, array $commanders, array $postRectors)
    {
        // A. slowly remove...
        $commanders = array_merge($commanders, $postRectors);

        $this->sortByPriorityAndSetCommanders($commanders);
        $this->currentFileInfoProvider = $currentFileInfoProvider;

        // B. refactor into ↓
        foreach ($commanders as $commander) {
            if ($commander instanceof PostRectorInterface) {
                $this->addVisitor($commander);
            }
        }
    }

    /**
     * @param Node[] $nodes
     * @return Node[]
     */
    public function traverseNodes(array $nodes): array
    {
        $this->setCurrentFileInfo($nodes);

        // A. commanders
        foreach ($this->commanders as $commander) {
            if (! $commander instanceof CommanderInterface) {
                continue;
            }

            if (! $commander->isActive()) {
                continue;
            }

            $nodes = $commander->traverseNodes($nodes);
        }

        // B. post rectors
        return parent::traverse($nodes);
    }

    /**
     * @param CommanderInterface[]|PostRectorInterface[] $commanders
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
