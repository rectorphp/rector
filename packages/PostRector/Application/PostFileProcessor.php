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
use Symfony\Component\Console\Style\SymfonyStyle;
use Symplify\Skipper\Skipper\Skipper;

final class PostFileProcessor
{
    /**
     * @var PostRectorInterface[]
     */
    private array $postRectors = [];

    /**
     * @param PostRectorInterface[] $postRectors
     */
    public function __construct(
        private readonly Skipper $skipper,
        private readonly CurrentFileProvider $currentFileProvider,
        private readonly CurrentRectorProvider $currentRectorProvider,
        private readonly SymfonyStyle $symfonyStyle,
        array $postRectors
    ) {
        $this->postRectors = $this->sortByPriority($postRectors);
    }

    /**
     * @param Stmt[] $stmts
     * @return Stmt[]
     */
    public function traverse(array $stmts): array
    {
        foreach ($this->postRectors as $postRector) {
            if ($this->shouldSkipPostRector($postRector)) {
                continue;
            }

            $this->currentRectorProvider->changeCurrentRector($postRector);
            $this->notifyPostRector($postRector);

            $nodeTraverser = new NodeTraverser();
            $nodeTraverser->addVisitor($postRector);
            $stmts = $nodeTraverser->traverse($stmts);
        }

        return $stmts;
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

    private function notifyPostRector(PostRectorInterface $postRector): void
    {
        if (! $this->symfonyStyle->isVerbose()) {
            return;
        }

        $message = sprintf('    [%s] %s', 'post rector', $postRector::class);
        $this->symfonyStyle->writeln($message);
    }
}
