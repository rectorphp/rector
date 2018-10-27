<?php declare(strict_types=1);

namespace Rector\Printer;

use Nette\Utils\FileSystem;
use PhpParser\Node;
use PhpParser\PrettyPrinter\Standard;
use Symplify\PackageBuilder\FileSystem\SmartFileInfo;

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
    public function printToFile(SmartFileInfo $fileInfo, array $newStmts, array $oldStmts, array $oldTokens): string
    {
        $newContent = $this->printToString($newStmts, $oldStmts, $oldTokens);

        FileSystem::write($fileInfo->getRealPath(), $newContent);

        return $newContent;
    }

    /**
     * @param Node[] $newStmts
     * @param Node[] $oldStmts
     * @param Node[] $oldTokens
     */
    public function printToString(array $newStmts, array $oldStmts, array $oldTokens): string
    {
        $printedContent = $this->betterStandardPrinter->printFormatPreserving($newStmts, $oldStmts, $oldTokens);

        return trim($printedContent) . PHP_EOL;
    }
}
