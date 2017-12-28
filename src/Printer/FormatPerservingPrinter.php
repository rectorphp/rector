<?php declare(strict_types=1);

namespace Rector\Printer;

use PhpParser\Node;
use PhpParser\PrettyPrinter\Standard;
use SplFileInfo;

final class FormatPerservingPrinter
{
    /**
     * @var BetterStandardPrinter|Standard
     */
    private $betterStandardPrinter;

    public function __construct(BetterStandardPrinter $betterStandardPrinter)
    {
        $this->betterStandardPrinter = $betterStandardPrinter;
    }

    /**
     * @param Node[] $newStmts
     * @param Node[] $oldStmts
     * @param Node[] $oldTokens
     */
    public function printToFile(SplFileInfo $fileInfo, array $newStmts, array $oldStmts, array $oldTokens): bool
    {
        $oldContent = file_get_contents($fileInfo->getRealPath());
        $newContent = $this->printToString($newStmts, $oldStmts, $oldTokens);

        if ($oldContent === $newContent) {
            return false;
        }

        return file_put_contents($fileInfo->getRealPath(), $newContent);
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
