<?php

declare(strict_types=1);

namespace Rector\PostRector\Application;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\FileSystem\CurrentFileInfoProvider;
use Rector\PostRector\Contract\Rector\PostRectorInterface;
use Symplify\Skipper\Skipper\Skipper;
use Symplify\SmartFileSystem\SmartFileInfo;

final class PostFileProcessor
{
    /**
     * @var PostRectorInterface[]
     */
    private $postRectors = [];

    /**
     * @var Skipper
     */
    private $skipper;

    /**
     * @var CurrentFileInfoProvider
     */
    private $currentFileInfoProvider;

    /**
     * @param PostRectorInterface[] $postRectors
     */
    public function __construct(Skipper $skipper, CurrentFileInfoProvider $currentFileInfoProvider, array $postRectors)
    {
        $this->postRectors = $this->sortByPriority($postRectors);
        $this->skipper = $skipper;
        $this->currentFileInfoProvider = $currentFileInfoProvider;
    }

    /**
     * @param Node[] $nodes
     * @return Node[]
     */
    public function traverse(array $nodes): array
    {
        foreach ($this->postRectors as $postRector) {
            if ($this->shouldSkipPostRector($postRector)) {
                continue;
            }

            $nodeTraverser = new NodeTraverser();
            $nodeTraverser->addVisitor($postRector);
            $nodes = $nodeTraverser->traverse($nodes);
        }

        return $nodes;
    }

    /**
     * @param PostRectorInterface[] $postRectors
     * @return PostRectorInterface[]
     */
    private function sortByPriority(array $postRectors): array
    {
        $postRectorsByPriority = [];

        foreach ($postRectors as $postRector) {
            if (isset($postRectorsByPriority[$postRector->getPriority()])) {
                throw new ShouldNotHappenException();
            }

            $postRectorsByPriority[$postRector->getPriority()] = $postRector;
        }

        krsort($postRectorsByPriority);

        return $postRectorsByPriority;
    }

    private function shouldSkipPostRector(PostRectorInterface $postRector): bool
    {
        $smartFileInfo = $this->currentFileInfoProvider->getSmartFileInfo();
        if (! $smartFileInfo instanceof SmartFileInfo) {
            return false;
        }

        return $this->skipper->shouldSkipElementAndFileInfo($postRector, $smartFileInfo);
    }
}
