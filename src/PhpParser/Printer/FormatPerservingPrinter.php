<?php

declare (strict_types=1);
namespace Rector\Core\PhpParser\Printer;

use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Namespace_;
use Rector\Core\PhpParser\Node\CustomNode\FileWithoutNamespace;
use Rector\Core\ValueObject\Application\File;
use Symplify\SmartFileSystem\SmartFileInfo;
use RectorPrefix20220418\Symplify\SmartFileSystem\SmartFileSystem;
/**
 * @see \Rector\Core\Tests\PhpParser\Printer\FormatPerservingPrinterTest
 */
final class FormatPerservingPrinter
{
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Printer\BetterStandardPrinter
     */
    private $betterStandardPrinter;
    /**
     * @readonly
     * @var \Symplify\SmartFileSystem\SmartFileSystem
     */
    private $smartFileSystem;
    public function __construct(\Rector\Core\PhpParser\Printer\BetterStandardPrinter $betterStandardPrinter, \RectorPrefix20220418\Symplify\SmartFileSystem\SmartFileSystem $smartFileSystem)
    {
        $this->betterStandardPrinter = $betterStandardPrinter;
        $this->smartFileSystem = $smartFileSystem;
    }
    /**
     * @param Node[] $newStmts
     * @param Node[] $oldStmts
     * @param Node[] $oldTokens
     */
    public function printToFile(\Symplify\SmartFileSystem\SmartFileInfo $fileInfo, array $newStmts, array $oldStmts, array $oldTokens) : string
    {
        $newContent = $this->betterStandardPrinter->printFormatPreserving($newStmts, $oldStmts, $oldTokens);
        $this->smartFileSystem->dumpFile($fileInfo->getRealPath(), $newContent);
        $this->smartFileSystem->chmod($fileInfo->getRealPath(), $fileInfo->getPerms());
        return $newContent;
    }
    public function printParsedStmstAndTokensToString(\Rector\Core\ValueObject\Application\File $file) : string
    {
        $newStmts = $this->resolveNewStmts($file);
        return $this->betterStandardPrinter->printFormatPreserving($newStmts, $file->getOldStmts(), $file->getOldTokens());
    }
    public function printParsedStmstAndTokens(\Rector\Core\ValueObject\Application\File $file) : string
    {
        $newStmts = $this->resolveNewStmts($file);
        return $this->printToFile($file->getSmartFileInfo(), $newStmts, $file->getOldStmts(), $file->getOldTokens());
    }
    /**
     * @return Stmt[]|mixed[]
     */
    private function resolveNewStmts(\Rector\Core\ValueObject\Application\File $file) : array
    {
        $newStmts = $file->getNewStmts();
        if (\count($newStmts) !== 1) {
            return $newStmts;
        }
        /** @var Namespace_|FileWithoutNamespace $onlyStmt */
        $onlyStmt = $newStmts[0];
        if (!$onlyStmt instanceof \Rector\Core\PhpParser\Node\CustomNode\FileWithoutNamespace) {
            return $newStmts;
        }
        return $onlyStmt->stmts;
    }
}
