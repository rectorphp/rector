<?php

declare (strict_types=1);
namespace Rector\PostRector\Application;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use Rector\PostRector\Contract\Rector\PostRectorInterface;
use Rector\PostRector\Rector\ClassRenamingPostRector;
use Rector\PostRector\Rector\NameImportingPostRector;
use Rector\PostRector\Rector\UnusedImportRemovingPostRector;
use Rector\PostRector\Rector\UseAddingPostRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Skipper\Skipper\Skipper;
final class PostFileProcessor
{
    /**
     * @readonly
     * @var \Rector\Skipper\Skipper\Skipper
     */
    private $skipper;
    /**
     * @var PostRectorInterface[]
     */
    private $postRectors = [];
    public function __construct(
        Skipper $skipper,
        // set order here
        UseAddingPostRector $useAddingPostRector,
        NameImportingPostRector $nameImportingPostRector,
        ClassRenamingPostRector $classRenamingPostRector,
        UnusedImportRemovingPostRector $unusedImportRemovingPostRector
    )
    {
        $this->skipper = $skipper;
        $this->postRectors = [
            // priority: 650
            $classRenamingPostRector,
            // priority: 600
            $nameImportingPostRector,
            // priority: 500
            $useAddingPostRector,
            // priority: 100
            $unusedImportRemovingPostRector,
        ];
    }
    /**
     * @param Node[] $stmts
     * @return Node[]
     */
    public function traverse(array $stmts, string $filePath) : array
    {
        foreach ($this->postRectors as $postRector) {
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
}
