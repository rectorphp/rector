<?php

declare (strict_types=1);
namespace Rector\PhpParser\Printer;

use PhpParser\Node;
use Rector\ValueObject\Application\File;
use RectorPrefix202401\Symfony\Component\Filesystem\Filesystem;
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
    /**
     * @readonly
     * @var \Symfony\Component\Filesystem\Filesystem
     */
    private $filesystem;
    public function __construct(\Rector\PhpParser\Printer\BetterStandardPrinter $betterStandardPrinter, Filesystem $filesystem)
    {
        $this->betterStandardPrinter = $betterStandardPrinter;
        $this->filesystem = $filesystem;
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
        $this->filesystem->dumpFile($filePath, $newContent);
    }
}
