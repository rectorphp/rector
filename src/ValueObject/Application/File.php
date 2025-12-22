<?php

declare (strict_types=1);
namespace Rector\ValueObject\Application;

use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\InlineHTML;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\NodeFinder;
use PhpParser\Token;
use Rector\ChangesReporting\ValueObject\RectorWithLineChange;
use Rector\Exception\ShouldNotHappenException;
use Rector\PhpParser\Node\FileNode;
use Rector\ValueObject\Reporting\FileDiff;
final class File
{
    /**
     * @readonly
     */
    private string $filePath;
    private string $fileContent;
    private bool $hasChanged = \false;
    /**
     * @readonly
     */
    private string $originalFileContent;
    private ?FileDiff $fileDiff = null;
    /**
     * @var Node[]
     */
    private array $oldStmts = [];
    /**
     * @var Node[]
     */
    private array $newStmts = [];
    /**
     * @var array<int, Token>
     */
    private array $oldTokens = [];
    /**
     * @var RectorWithLineChange[]
     */
    private array $rectorWithLineChanges = [];
    /**
     * Cached result per file
     */
    private ?bool $containsHtml = null;
    public function __construct(string $filePath, string $fileContent)
    {
        $this->filePath = $filePath;
        $this->fileContent = $fileContent;
        $this->originalFileContent = $fileContent;
    }
    public function getFilePath(): string
    {
        return $this->filePath;
    }
    public function getFileContent(): string
    {
        return $this->fileContent;
    }
    public function changeFileContent(string $newFileContent): void
    {
        if ($this->fileContent === $newFileContent) {
            return;
        }
        $this->fileContent = $newFileContent;
        $this->hasChanged = \true;
    }
    public function getOriginalFileContent(): string
    {
        return $this->originalFileContent;
    }
    public function hasChanged(): bool
    {
        return $this->hasChanged;
    }
    public function changeHasChanged(bool $status): void
    {
        $this->hasChanged = $status;
    }
    public function setFileDiff(FileDiff $fileDiff): void
    {
        $this->fileDiff = $fileDiff;
    }
    public function getFileDiff(): ?FileDiff
    {
        return $this->fileDiff;
    }
    /**
     * @param Stmt[] $newStmts
     * @param Stmt[] $oldStmts
     * @param array<int, Token> $oldTokens
     */
    public function hydrateStmtsAndTokens(array $newStmts, array $oldStmts, array $oldTokens): void
    {
        if ($this->oldStmts !== []) {
            throw new ShouldNotHappenException('Double stmts override');
        }
        $this->oldStmts = $oldStmts;
        $this->newStmts = $newStmts;
        $this->oldTokens = $oldTokens;
    }
    /**
     * @return Stmt[]
     */
    public function getOldStmts(): array
    {
        return $this->oldStmts;
    }
    /**
     * @return Stmt[]
     */
    public function getNewStmts(): array
    {
        return $this->newStmts;
    }
    /**
     * @return array<int, Token>
     */
    public function getOldTokens(): array
    {
        return $this->oldTokens;
    }
    /**
     * @param Node[] $newStmts
     */
    public function changeNewStmts(array $newStmts): void
    {
        $this->newStmts = $newStmts;
    }
    public function addRectorClassWithLine(RectorWithLineChange $rectorWithLineChange): void
    {
        $this->rectorWithLineChanges[] = $rectorWithLineChange;
    }
    /**
     * This node returns top most node,
     * that includes use imports
     * @return \PhpParser\Node\Stmt\Namespace_|\Rector\PhpParser\Node\FileNode|null
     */
    public function getUseImportsRootNode()
    {
        if ($this->newStmts === []) {
            return null;
        }
        $firstStmt = $this->newStmts[0];
        if ($firstStmt instanceof FileNode) {
            if (!$firstStmt->isNamespaced()) {
                return $firstStmt;
            }
            // return sole Namespace, or none
            $namespaces = [];
            foreach ($firstStmt->stmts as $stmt) {
                if ($stmt instanceof Namespace_) {
                    $namespaces[] = $stmt;
                }
            }
            if (count($namespaces) === 1) {
                return $namespaces[0];
            }
        }
        return null;
    }
    /**
     * @return RectorWithLineChange[]
     */
    public function getRectorWithLineChanges(): array
    {
        return $this->rectorWithLineChanges;
    }
    public function containsHTML(): bool
    {
        if ($this->containsHtml !== null) {
            return $this->containsHtml;
        }
        $nodeFinder = new NodeFinder();
        $this->containsHtml = (bool) $nodeFinder->findFirstInstanceOf($this->oldStmts, InlineHTML::class);
        return $this->containsHtml;
    }
    public function getFileNode(): ?FileNode
    {
        if ($this->newStmts === []) {
            return null;
        }
        if ($this->newStmts[0] instanceof FileNode) {
            return $this->newStmts[0];
        }
        return null;
    }
    public function hasShebang(): bool
    {
        return strncmp($this->fileContent, '#!', strlen('#!')) === 0;
    }
}
