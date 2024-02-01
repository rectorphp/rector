<?php

declare (strict_types=1);
namespace Rector\PhpParser\Printer;

use RectorPrefix202402\Nette\Utils\FileSystem;
use PhpParser\Node;
use Rector\ValueObject\Application\File;
/**
 * @see \Rector\Tests\PhpParser\Printer\FormatPerservingPrinterTest
 */
final class FormatPerservingPrinter
{
    /**
     * @readonly
     * @var \Rector\PhpParser\Printer\BetterStandardPrinter
     */
    private $betterStandardPrinter;
    public function __construct(\Rector\PhpParser\Printer\BetterStandardPrinter $betterStandardPrinter)
    {
        $this->betterStandardPrinter = $betterStandardPrinter;
    }
    /**
     * @api tests
     *
     * @param Node[] $newStmts
     * @param Node[] $oldStmts
     * @param Node[] $oldTokens
     */
    public function printToFile(string $filePath, array $newStmts, array $oldStmts, array $oldTokens) : string
    {
        $newContent = $this->betterStandardPrinter->printFormatPreserving($newStmts, $oldStmts, $oldTokens);
        $this->dumpFile($filePath, $newContent);
        return $newContent;
    }
    public function printParsedStmstAndTokensToString(File $file) : string
    {
        return $this->betterStandardPrinter->printFormatPreserving($file->getNewStmts(), $file->getOldStmts(), $file->getOldTokens());
    }
    public function dumpFile(string $filePath, string $newContent) : void
    {
        FileSystem::write($filePath, $newContent, null);
    }
}
