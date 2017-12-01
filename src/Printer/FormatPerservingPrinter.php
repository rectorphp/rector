<?php declare(strict_types=1);

namespace Rector\Printer;

use PhpParser\Node;
use SplFileInfo;

final class FormatPerservingPrinter
{
    /**
     * @var BetterStandardPrinter
     */
    private $betterStandardPrinter;

    /**
     * @var ChangedFilesCollector
     */
    private $changedFilesCollector;

    public function __construct(
        BetterStandardPrinter $betterStandardPrinter,
        ChangedFilesCollector $changedFilesCollector
    ) {
        $this->betterStandardPrinter = $betterStandardPrinter;
        $this->changedFilesCollector = $changedFilesCollector;
    }

    /**
     * @param Node[] $newStmts
     * @param Node[] $oldStmts
     * @param Node[] $oldTokens
     */
    public function printToFile(SplFileInfo $fileInfo, array $newStmts, array $oldStmts, array $oldTokens): void
    {
        $oldContent = file_get_contents($fileInfo->getRealPath());
        $newContent = $this->printToString($newStmts, $oldStmts, $oldTokens);

        if ($oldContent === $newContent) {
            return;
        }

        $this->changedFilesCollector->addChangedFile($fileInfo);

        file_put_contents($fileInfo->getRealPath(), $newContent);
    }

    /**
     * @param Node[] $newStmts
     * @param Node[] $oldStmts
     * @param Node[] $oldTokens
     */
    public function printToString(array $newStmts, array $oldStmts, array $oldTokens): string
    {
        return $this->betterStandardPrinter->printFormatPreserving($newStmts, $oldStmts, $oldTokens);
    }
}
