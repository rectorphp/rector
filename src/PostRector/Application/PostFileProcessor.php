<?php

declare (strict_types=1);
namespace Rector\PostRector\Application;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use Rector\Configuration\Option;
use Rector\Configuration\Parameter\SimpleParameterProvider;
use Rector\Contract\DependencyInjection\ResetableInterface;
use Rector\PostRector\Contract\Rector\PostRectorInterface;
use Rector\PostRector\Rector\ClassRenamingPostRector;
use Rector\PostRector\Rector\NameImportingPostRector;
use Rector\PostRector\Rector\UnusedImportRemovingPostRector;
use Rector\PostRector\Rector\UseAddingPostRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Skipper\Skipper\Skipper;
final class PostFileProcessor implements ResetableInterface
{
    /**
     * @readonly
     * @var \Rector\Skipper\Skipper\Skipper
     */
    private $skipper;
    /**
     * @readonly
     * @var \Rector\PostRector\Rector\UseAddingPostRector
     */
    private $useAddingPostRector;
    /**
     * @readonly
     * @var \Rector\PostRector\Rector\NameImportingPostRector
     */
    private $nameImportingPostRector;
    /**
     * @readonly
     * @var \Rector\PostRector\Rector\ClassRenamingPostRector
     */
    private $classRenamingPostRector;
    /**
     * @readonly
     * @var \Rector\PostRector\Rector\UnusedImportRemovingPostRector
     */
    private $unusedImportRemovingPostRector;
    /**
     * @var PostRectorInterface[]
     */
    private $postRectors = [];
    public function __construct(Skipper $skipper, UseAddingPostRector $useAddingPostRector, NameImportingPostRector $nameImportingPostRector, ClassRenamingPostRector $classRenamingPostRector, UnusedImportRemovingPostRector $unusedImportRemovingPostRector)
    {
        $this->skipper = $skipper;
        $this->useAddingPostRector = $useAddingPostRector;
        $this->nameImportingPostRector = $nameImportingPostRector;
        $this->classRenamingPostRector = $classRenamingPostRector;
        $this->unusedImportRemovingPostRector = $unusedImportRemovingPostRector;
    }
    public function reset() : void
    {
        $this->postRectors = [];
    }
    /**
     * @param Node[] $stmts
     * @return Node[]
     */
    public function traverse(array $stmts, string $filePath) : array
    {
        foreach ($this->getPostRectors() as $postRector) {
            if ($this->shouldSkipPostRector($postRector, $filePath)) {
                continue;
            }
            $nodeTraverser = new NodeTraverser();
            $nodeTraverser->addVisitor($postRector);
            $stmts = $nodeTraverser->traverse($stmts);
        }
        return $stmts;
    }
    private function shouldSkipPostRector(PostRectorInterface $postRector, string $filePath) : bool
    {
        if ($this->skipper->shouldSkipElementAndFilePath($postRector, $filePath)) {
            return \true;
        }
        // skip renaming if rename class rector is skipped
        return $postRector instanceof ClassRenamingPostRector && $this->skipper->shouldSkipElementAndFilePath(RenameClassRector::class, $filePath);
    }
    /**
     * Load on the fly, to allow test reset with different configuration
     * @return PostRectorInterface[]
     */
    private function getPostRectors() : array
    {
        if ($this->postRectors !== []) {
            return $this->postRectors;
        }
        $isNameImportingEnabled = SimpleParameterProvider::provideBoolParameter(Option::AUTO_IMPORT_NAMES);
        $isRemovingUnusedImportsEnabled = SimpleParameterProvider::provideBoolParameter(Option::REMOVE_UNUSED_IMPORTS);
        // sorted by priority, to keep removed imports in order
        $postRectors = [$this->classRenamingPostRector];
        // import docblocks
        if ($isNameImportingEnabled) {
            $postRectors[] = $this->nameImportingPostRector;
        }
        $postRectors[] = $this->useAddingPostRector;
        if ($isRemovingUnusedImportsEnabled) {
            $postRectors[] = $this->unusedImportRemovingPostRector;
        }
        $this->postRectors = $postRectors;
        return $this->postRectors;
    }
}
