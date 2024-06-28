<?php

declare (strict_types=1);
namespace Rector\PostRector\Application;

use PhpParser\Node\Stmt;
use PhpParser\NodeTraverser;
use Rector\Configuration\Option;
use Rector\Configuration\Parameter\SimpleParameterProvider;
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
     * @var \Rector\PostRector\Rector\DocblockNameImportingPostRector
     */
    private $docblockNameImportingPostRector;
    /**
     * @readonly
     * @var \Rector\PostRector\Rector\UnusedImportRemovingPostRector
     */
    private $unusedImportRemovingPostRector;
    /**
     * @var PostRectorInterface[]
     */
    private $postRectors = [];
    public function __construct(Skipper $skipper, UseAddingPostRector $useAddingPostRector, NameImportingPostRector $nameImportingPostRector, ClassRenamingPostRector $classRenamingPostRector, DocblockNameImportingPostRector $docblockNameImportingPostRector, UnusedImportRemovingPostRector $unusedImportRemovingPostRector)
    {
        $this->skipper = $skipper;
        $this->useAddingPostRector = $useAddingPostRector;
        $this->nameImportingPostRector = $nameImportingPostRector;
        $this->classRenamingPostRector = $classRenamingPostRector;
        $this->docblockNameImportingPostRector = $docblockNameImportingPostRector;
        $this->unusedImportRemovingPostRector = $unusedImportRemovingPostRector;
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
            if ($this->shouldSkipPostRector($postRector, $file->getFilePath(), $stmts)) {
                continue;
            }
            $postRector->setFile($file);
            $nodeTraverser = new NodeTraverser();
            $nodeTraverser->addVisitor($postRector);
            $stmts = $nodeTraverser->traverse($stmts);
        }
        return $stmts;
    }
    /**
     * @param Stmt[] $stmts
     */
    private function shouldSkipPostRector(PostRectorInterface $postRector, string $filePath, array $stmts) : bool
    {
        if (!$postRector->shouldTraverse($stmts)) {
            return \true;
        }
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
        $isDocblockNameImportingEnabled = SimpleParameterProvider::provideBoolParameter(Option::AUTO_IMPORT_DOC_BLOCK_NAMES);
        $isRemovingUnusedImportsEnabled = SimpleParameterProvider::provideBoolParameter(Option::REMOVE_UNUSED_IMPORTS);
        // sorted by priority, to keep removed imports in order
        $postRectors = [$this->classRenamingPostRector];
        // import names
        if ($isNameImportingEnabled) {
            $postRectors[] = $this->nameImportingPostRector;
        }
        // import docblocks
        if ($isDocblockNameImportingEnabled) {
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
