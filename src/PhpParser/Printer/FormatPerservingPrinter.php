<?php

declare(strict_types=1);

namespace Rector\Core\PhpParser\Printer;

use PhpParser\Node;
use PhpParser\Node\Stmt;
use Rector\Core\PhpParser\Node\CustomNode\FileWithoutNamespace;
use Rector\Core\ValueObject\Application\File;
use Symplify\SmartFileSystem\SmartFileInfo;
use Symplify\SmartFileSystem\SmartFileSystem;

/**
 * @see \Rector\Core\Tests\PhpParser\Printer\FormatPerservingPrinterTest
 */
final class FormatPerservingPrinter
{
    public function __construct(
        private BetterStandardPrinter $betterStandardPrinter,
        private SmartFileSystem $smartFileSystem
    ) {
    }

    /**
     * @param Node[] $newStmts
     * @param Node[] $oldStmts
     * @param Node[] $oldTokens
     */
    public function printToFile(SmartFileInfo $fileInfo, array $newStmts, array $oldStmts, array $oldTokens): string
    {
        $newContent = $this->betterStandardPrinter->printFormatPreserving($newStmts, $oldStmts, $oldTokens);

        $this->smartFileSystem->dumpFile($fileInfo->getRealPath(), $newContent);
        $this->smartFileSystem->chmod($fileInfo->getRealPath(), $fileInfo->getPerms());

        return $newContent;
    }

    public function printParsedStmstAndTokensToString(File $file): string
    {
        $newStmts = $this->resolveNewStmts($file);

        return $this->betterStandardPrinter->printFormatPreserving(
            $newStmts,
            $file->getOldStmts(),
            $file->getOldTokens()
        );
    }

    public function printParsedStmstAndTokens(File $file): string
    {
        return $this->printToFile(
            $file->getSmartFileInfo(),
            $file->getNewStmts(),
            $file->getOldStmts(),
            $file->getOldTokens()
        );
    }

    /**
     * @return Stmt[]|mixed[]
     */
    private function resolveNewStmts(File $file): array
    {
        if (count($file->getNewStmts()) === 1) {
            $onlyStmt = $file->getNewStmts()[0];
            if ($onlyStmt instanceof FileWithoutNamespace) {
                return $onlyStmt->stmts;
            }
        }

        return $file->getNewStmts();
    }
}
