<?php

declare (strict_types=1);
namespace Rector\Core\ValueObject\Application;

use PhpParser\Node;
use PhpParser\Node\Stmt;
use Rector\ChangesReporting\ValueObject\RectorWithLineChange;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\ValueObject\Reporting\FileDiff;
/**
 * @see \Rector\Core\ValueObjectFactory\Application\FileFactory
 */
final class File
{
    /**
     * @readonly
     * @var string
     */
    private $filePath;
    /**
     * @var string
     */
    private $fileContent;
    /**
     * @var bool
     */
    private $hasChanged = \false;
    /**
     * @readonly
     * @var string
     */
    private $originalFileContent;
    /**
     * @var \Rector\Core\ValueObject\Reporting\FileDiff|null
     */
    private $fileDiff;
    /**
     * @var Node[]
     */
    private $oldStmts = [];
    /**
     * @var Node[]
     */
    private $newStmts = [];
    /**
     * @var array<int, array{int, string, int}|string>
     */
    private $oldTokens = [];
    /**
     * @var RectorWithLineChange[]
     */
    private $rectorWithLineChanges = [];
    public function __construct(string $filePath, string $fileContent)
    {
        $this->filePath = $filePath;
        $this->fileContent = $fileContent;
        $this->originalFileContent = $fileContent;
    }
    public function getFilePath() : string
    {
        return $this->filePath;
    }
    public function getFileContent() : string
    {
        return $this->fileContent;
    }
    public function changeFileContent(string $newFileContent) : void
    {
        if ($this->fileContent === $newFileContent) {
            return;
        }
        $this->fileContent = $newFileContent;
        $this->hasChanged = \true;
    }
    public function getOriginalFileContent() : string
    {
        return $this->originalFileContent;
    }
    public function hasChanged() : bool
    {
        return $this->hasChanged;
    }
    public function changeHasChanged(bool $status) : void
    {
        $this->hasChanged = $status;
    }
    public function setFileDiff(FileDiff $fileDiff) : void
    {
        $this->fileDiff = $fileDiff;
    }
    public function getFileDiff() : ?FileDiff
    {
        return $this->fileDiff;
    }
    /**
     * @param Stmt[] $newStmts
     * @param Stmt[] $oldStmts
     * @param array<int, array{int, string, int}|string> $oldTokens
     */
    public function hydrateStmtsAndTokens(array $newStmts, array $oldStmts, array $oldTokens) : void
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
    public function getOldStmts() : array
    {
        return $this->oldStmts;
    }
    /**
     * @return Stmt[]
     */
    public function getNewStmts() : array
    {
        return $this->newStmts;
    }
    /**
     * @return array<int, array{int, string, int}|string>
     */
    public function getOldTokens() : array
    {
        return $this->oldTokens;
    }
    /**
     * @param Node[] $newStmts
     */
    public function changeNewStmts(array $newStmts) : void
    {
        $this->newStmts = $newStmts;
    }
    public function addRectorClassWithLine(RectorWithLineChange $rectorWithLineChange) : void
    {
        $this->rectorWithLineChanges[] = $rectorWithLineChange;
    }
    /**
     * @return RectorWithLineChange[]
     */
    public function getRectorWithLineChanges() : array
    {
        return $this->rectorWithLineChanges;
    }
}
