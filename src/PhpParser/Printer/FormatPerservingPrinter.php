<?php

declare(strict_types=1);

namespace Rector\Core\PhpParser\Printer;

use PhpParser\Node;
use PhpParser\Node\Stmt;
use Rector\Core\PhpParser\Node\CustomNode\FileWithoutNamespace;
use Rector\Core\ValueObject\Application\ParsedStmtsAndTokens;
use Symplify\SmartFileSystem\SmartFileInfo;
use Symplify\SmartFileSystem\SmartFileSystem;

/**
 * @see \Rector\Core\Tests\PhpParser\Printer\FormatPerservingPrinterTest
 */
final class FormatPerservingPrinter
{
    /**
     * @var BetterStandardPrinter
     */
    private $betterStandardPrinter;

    /**
     * @var SmartFileSystem
     */
    private $smartFileSystem;

    public function __construct(BetterStandardPrinter $betterStandardPrinter, SmartFileSystem $smartFileSystem)
    {
        $this->betterStandardPrinter = $betterStandardPrinter;
        $this->smartFileSystem = $smartFileSystem;
    }

    public function printParsedStmstAndTokensToString(ParsedStmtsAndTokens $parsedStmtsAndTokens): string
    {
        $newStmts = $this->resolveNewStmts($parsedStmtsAndTokens);

        return $this->betterStandardPrinter->printFormatPreserving(
            $newStmts,
            $parsedStmtsAndTokens->getOldStmts(),
            $parsedStmtsAndTokens->getOldTokens()
        );
    }

    public function printParsedStmstAndTokens(
        SmartFileInfo $smartFileInfo,
        ParsedStmtsAndTokens $parsedStmtsAndTokens
    ): string {
        return $this->printToFile(
            $smartFileInfo,
            $parsedStmtsAndTokens->getNewStmts(),
            $parsedStmtsAndTokens->getOldStmts(),
            $parsedStmtsAndTokens->getOldTokens()
        );
    }

    /**
     * @param Node[] $newStmts
     * @param Node[] $oldStmts
     * @param Node[] $oldTokens
     */
    private function printToFile(SmartFileInfo $fileInfo, array $newStmts, array $oldStmts, array $oldTokens): string
    {
        $newContent = $this->printToString($newStmts, $oldStmts, $oldTokens);

        $this->smartFileSystem->dumpFile($fileInfo->getRealPath(), $newContent);
        $this->smartFileSystem->chmod($fileInfo->getRealPath(), $fileInfo->getPerms());

        return $newContent;
    }

    /**
     * @param Node[] $newStmts
     * @param Node[] $oldStmts
     * @param Node[] $oldTokens
     */
    private function printToString(array $newStmts, array $oldStmts, array $oldTokens): string
    {
        return $this->betterStandardPrinter->printFormatPreserving($newStmts, $oldStmts, $oldTokens);
    }

    /**
     * @return Stmt[]|Node[]
     */
    private function resolveNewStmts(ParsedStmtsAndTokens $parsedStmtsAndTokens): array
    {
        if (count($parsedStmtsAndTokens->getNewStmts()) === 1) {
            $onlyStmt = $parsedStmtsAndTokens->getNewStmts()[0];
            if ($onlyStmt instanceof FileWithoutNamespace) {
                return $onlyStmt->stmts;
            }
        }

        return $parsedStmtsAndTokens->getNewStmts();
    }
}
