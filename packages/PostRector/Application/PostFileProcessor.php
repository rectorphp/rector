<?php

declare (strict_types=1);
namespace Rector\PostRector\Application;

use PhpParser\Node\Stmt;
use PhpParser\NodeTraverser;
use Rector\Core\Logging\CurrentRectorProvider;
use Rector\Core\Provider\CurrentFileProvider;
use Rector\Core\ValueObject\Application\File;
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
     * @var PostRectorInterface[]
     */
    private $postRectors = [];
    public function __construct(
        Skipper $skipper,
        CurrentFileProvider $currentFileProvider,
        CurrentRectorProvider $currentRectorProvider,
        // set order here
        UseAddingPostRector $useAddingPostRector,
        NameImportingPostRector $nameImportingPostRector,
        ClassRenamingPostRector $classRenamingPostRector,
        UnusedImportRemovingPostRector $unusedImportRemovingPostRector
    )
    {
        $this->skipper = $skipper;
        $this->currentFileProvider = $currentFileProvider;
        $this->currentRectorProvider = $currentRectorProvider;
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
    private function shouldSkipPostRector(PostRectorInterface $postRector) : bool
    {
        $file = $this->currentFileProvider->getFile();
        if (!$file instanceof File) {
            return \false;
        }
        $filePath = $file->getFilePath();
        if ($this->skipper->shouldSkipElementAndFilePath($postRector, $filePath)) {
            return \true;
        }
        // skip renaming if rename class rector is skipped
        return $postRector instanceof ClassRenamingPostRector && $this->skipper->shouldSkipElementAndFilePath(RenameClassRector::class, $filePath);
    }
}
