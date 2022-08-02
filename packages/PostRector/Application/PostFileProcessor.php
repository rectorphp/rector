<?php

declare (strict_types=1);
namespace Rector\PostRector\Application;

use PhpParser\Node\Stmt;
use PhpParser\NodeTraverser;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Logging\CurrentRectorProvider;
use Rector\Core\Provider\CurrentFileProvider;
use Rector\Core\ValueObject\Application\File;
use Rector\PostRector\Contract\Rector\PostRectorInterface;
use RectorPrefix202208\Symplify\Skipper\Skipper\Skipper;
final class PostFileProcessor
{
    /**
     * @var PostRectorInterface[]
     */
    private $postRectors = [];
    /**
     * @readonly
     * @var \Symplify\Skipper\Skipper\Skipper
     */
    private $skipper;
    /**
     * @readonly
     * @var \Rector\Core\Provider\CurrentFileProvider
     */
    private $currentFileProvider;
    /**
     * @readonly
     * @var \Rector\Core\Logging\CurrentRectorProvider
     */
    private $currentRectorProvider;
    /**
     * @param PostRectorInterface[] $postRectors
     */
    public function __construct(Skipper $skipper, CurrentFileProvider $currentFileProvider, CurrentRectorProvider $currentRectorProvider, array $postRectors)
    {
        $this->skipper = $skipper;
        $this->currentFileProvider = $currentFileProvider;
        $this->currentRectorProvider = $currentRectorProvider;
        $this->postRectors = $this->sortByPriority($postRectors);
    }
    /**
     * @param Stmt[] $stmts
     * @return Stmt[]
     */
    public function traverse(array $stmts) : array
    {
        foreach ($this->postRectors as $postRector) {
            if ($this->shouldSkipPostRector($postRector)) {
                continue;
            }
            $this->currentRectorProvider->changeCurrentRector($postRector);
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
    private function sortByPriority(array $postRectors) : array
    {
        $postRectorsByPriority = [];
        foreach ($postRectors as $postRector) {
            if (isset($postRectorsByPriority[$postRector->getPriority()])) {
                throw new ShouldNotHappenException();
            }
            $postRectorsByPriority[$postRector->getPriority()] = $postRector;
        }
        \krsort($postRectorsByPriority);
        return $postRectorsByPriority;
    }
    private function shouldSkipPostRector(PostRectorInterface $postRector) : bool
    {
        $file = $this->currentFileProvider->getFile();
        if (!$file instanceof File) {
            return \false;
        }
        $smartFileInfo = $file->getSmartFileInfo();
        return $this->skipper->shouldSkipElementAndFileInfo($postRector, $smartFileInfo);
    }
}
