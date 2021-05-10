<?php

declare(strict_types=1);

namespace Rector\PostRector\Application;

use PhpParser\Node\Stmt;
use PhpParser\NodeTraverser;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Logging\CurrentRectorProvider;
use Rector\Core\Provider\CurrentFileProvider;
use Rector\Core\ValueObject\Application\File;
use Rector\PostRector\Contract\Rector\PostRectorInterface;
use Symplify\Skipper\Skipper\Skipper;

final class PostFileProcessor
{
    /**
     * @var PostRectorInterface[]
     */
    private $postRectors = [];

    /**
     * @param PostRectorInterface[] $postRectors
     */
    public function __construct(
        private Skipper $skipper,
        private CurrentFileProvider $currentFileProvider,
        private CurrentRectorProvider $currentRectorProvider,
        array $postRectors
    ) {
        $this->postRectors = $this->sortByPriority($postRectors);
    }

    /**
     * @param Stmt[] $nodes
     * @return Stmt[]
     */
    public function traverse(array $nodes): array
    {
        foreach ($this->postRectors as $postRector) {
            if ($this->shouldSkipPostRector($postRector)) {
                continue;
            }

            $this->currentRectorProvider->changeCurrentRector($postRector);

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
        $file = $this->currentFileProvider->getFile();
        if (! $file instanceof File) {
            return false;
        }

        $smartFileInfo = $file->getSmartFileInfo();
        return $this->skipper->shouldSkipElementAndFileInfo($postRector, $smartFileInfo);
    }
}
