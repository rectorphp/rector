<?php

declare (strict_types=1);
namespace Rector\Core\ValueObject\Application;

use PhpParser\Node\Stmt;
use Rector\ChangesReporting\ValueObject\RectorWithLineChange;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\ValueObject\Reporting\FileDiff;
use Symplify\SmartFileSystem\SmartFileInfo;
/**
 * @see \Rector\Core\ValueObjectFactory\Application\FileFactory
 */
final class File
{
    /**
     * @var bool
     */
    private $hasChanged = \false;
    /**
     * @var string
     */
    private $originalFileContent;
    /**
     * @var \Rector\Core\ValueObject\Reporting\FileDiff|null
     */
    private $fileDiff;
    /**
     * @var Stmt[]
     */
    private $oldStmts = [];
    /**
     * @var Stmt[]
     */
    private $newStmts = [];
    /**
     * @var mixed[]
     */
    private $oldTokens = [];
    /**
     * @var RectorWithLineChange[]
     */
    private $rectorWithLineChanges = [];
    /**
     * @var RectorError[]
     */
    private $rectorErrors = [];
    /**
     * @var \Symplify\SmartFileSystem\SmartFileInfo
     */
    private $smartFileInfo;
    /**
     * @var string
     */
    private $fileContent;
    public function __construct(\Symplify\SmartFileSystem\SmartFileInfo $smartFileInfo, string $fileContent)
    {
        $this->smartFileInfo = $smartFileInfo;
        $this->fileContent = $fileContent;
        $this->originalFileContent = $fileContent;
    }
    public function getSmartFileInfo() : \Symplify\SmartFileSystem\SmartFileInfo
    {
        return $this->smartFileInfo;
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
    public function hasContentChanged() : bool
    {
        return $this->fileContent !== $this->originalFileContent;
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
    public function setFileDiff(\Rector\Core\ValueObject\Reporting\FileDiff $fileDiff) : void
    {
        $this->fileDiff = $fileDiff;
    }
    public function getFileDiff() : ?\Rector\Core\ValueObject\Reporting\FileDiff
    {
        return $this->fileDiff;
    }
    /**
     * @param Stmt[] $newStmts
     * @param Stmt[] $oldStmts
     * @param mixed[] $oldTokens
     */
    public function hydrateStmtsAndTokens(array $newStmts, array $oldStmts, array $oldTokens) : void
    {
        if ($this->oldStmts !== []) {
            throw new \Rector\Core\Exception\ShouldNotHappenException('Double stmts override');
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
     * @return mixed[]
     */
    public function getOldTokens() : array
    {
        return $this->oldTokens;
    }
    /**
     * @param Stmt[] $newStmts
     */
    public function changeNewStmts(array $newStmts) : void
    {
        $this->newStmts = $newStmts;
    }
    public function addRectorClassWithLine(\Rector\ChangesReporting\ValueObject\RectorWithLineChange $rectorWithLineChange) : void
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
    public function addRectorError(\Rector\Core\ValueObject\Application\RectorError $rectorError) : void
    {
        $this->rectorErrors[] = $rectorError;
    }
    public function hasErrors() : bool
    {
        return $this->rectorErrors !== [];
    }
    /**
     * @return RectorError[]
     */
    public function getErrors() : array
    {
        return $this->rectorErrors;
    }
}
