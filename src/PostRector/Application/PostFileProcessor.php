<?php

declare (strict_types=1);
namespace Rector\PostRector\Application;

use PhpParser\Node\Stmt;
use PhpParser\NodeTraverser;
use Rector\Configuration\Option;
use Rector\Configuration\Parameter\SimpleParameterProvider;
use Rector\Configuration\RenamedClassesDataCollector;
use Rector\Contract\DependencyInjection\ResetableInterface;
use Rector\PostRector\Contract\Rector\PostRectorInterface;
use Rector\PostRector\Rector\ClassRenamingPostRector;
use Rector\PostRector\Rector\DocblockNameImportingPostRector;
use Rector\PostRector\Rector\NameImportingPostRector;
use Rector\PostRector\Rector\UnusedImportRemovingPostRector;
use Rector\PostRector\Rector\UseAddingPostRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Skipper\Skipper\Skipper;
use Rector\ValueObject\Application\File;
final class PostFileProcessor implements ResetableInterface
{
    /**
     * @readonly
     */
    private Skipper $skipper;
    /**
     * @readonly
     */
    private UseAddingPostRector $useAddingPostRector;
    /**
     * @readonly
     */
    private NameImportingPostRector $nameImportingPostRector;
    /**
     * @readonly
     */
    private ClassRenamingPostRector $classRenamingPostRector;
    /**
     * @readonly
     */
    private DocblockNameImportingPostRector $docblockNameImportingPostRector;
    /**
     * @readonly
     */
    private UnusedImportRemovingPostRector $unusedImportRemovingPostRector;
    /**
     * @readonly
     */
    private RenamedClassesDataCollector $renamedClassesDataCollector;
    /**
     * @var PostRectorInterface[]
     */
    private array $postRectors = [];
    public function __construct(Skipper $skipper, UseAddingPostRector $useAddingPostRector, NameImportingPostRector $nameImportingPostRector, ClassRenamingPostRector $classRenamingPostRector, DocblockNameImportingPostRector $docblockNameImportingPostRector, UnusedImportRemovingPostRector $unusedImportRemovingPostRector, RenamedClassesDataCollector $renamedClassesDataCollector)
    {
        $this->skipper = $skipper;
        $this->useAddingPostRector = $useAddingPostRector;
        $this->nameImportingPostRector = $nameImportingPostRector;
        $this->classRenamingPostRector = $classRenamingPostRector;
        $this->docblockNameImportingPostRector = $docblockNameImportingPostRector;
        $this->unusedImportRemovingPostRector = $unusedImportRemovingPostRector;
        $this->renamedClassesDataCollector = $renamedClassesDataCollector;
    }
    public function reset() : void
    {
        $this->postRectors = [];
    }
    /**
     * @param Stmt[] $stmts
     * @return Stmt[]
     */
    public function traverse(array $stmts, File $file) : array
    {
        foreach ($this->getPostRectors() as $postRector) {
            // file must be set early into PostRector class to ensure its usage
            // always match on skipping process
            $postRector->setFile($file);
            if ($this->shouldSkipPostRector($postRector, $file->getFilePath(), $stmts)) {
                continue;
            }
            $nodeTraverser = new NodeTraverser($postRector);
            $stmts = $nodeTraverser->traverse($stmts);
        }
        return $stmts;
    }
    /**
     * @param Stmt[] $stmts
     */
    private function shouldSkipPostRector(PostRectorInterface $postRector, string $filePath, array $stmts) : bool
    {
        if ($this->skipper->shouldSkipElementAndFilePath($postRector, $filePath)) {
            return \true;
        }
        // skip renaming if rename class rector is skipped
        if ($postRector instanceof ClassRenamingPostRector && $this->skipper->shouldSkipElementAndFilePath(RenameClassRector::class, $filePath)) {
            return \true;
        }
        return !$postRector->shouldTraverse($stmts);
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
        $isRenamedClassEnabled = $this->renamedClassesDataCollector->getOldToNewClasses() !== [];
        $isNameImportingEnabled = SimpleParameterProvider::provideBoolParameter(Option::AUTO_IMPORT_NAMES);
        $isDocblockNameImportingEnabled = SimpleParameterProvider::provideBoolParameter(Option::AUTO_IMPORT_DOC_BLOCK_NAMES);
        $isRemovingUnusedImportsEnabled = SimpleParameterProvider::provideBoolParameter(Option::REMOVE_UNUSED_IMPORTS);
        $postRectors = [];
        // sorted by priority, to keep removed imports in order
        if ($isRenamedClassEnabled) {
            $postRectors[] = $this->classRenamingPostRector;
        }
        // import names
        if ($isNameImportingEnabled) {
            $postRectors[] = $this->nameImportingPostRector;
        }
        // import docblocks
        if ($isNameImportingEnabled && $isDocblockNameImportingEnabled) {
            $postRectors[] = $this->docblockNameImportingPostRector;
        }
        $postRectors[] = $this->useAddingPostRector;
        if ($isRemovingUnusedImportsEnabled) {
            $postRectors[] = $this->unusedImportRemovingPostRector;
        }
        $this->postRectors = $postRectors;
        return $this->postRectors;
    }
}
