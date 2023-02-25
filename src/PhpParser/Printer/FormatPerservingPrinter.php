<?php

declare (strict_types=1);
namespace Rector\Core\PhpParser\Printer;

use PhpParser\Node;
use Rector\Core\ValueObject\Application\File;
use RectorPrefix202302\Symfony\Component\Filesystem\Filesystem;
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
     * @var \Symfony\Component\Filesystem\Filesystem
     */
    private $filesystem;
    public function __construct(\Rector\Core\PhpParser\Printer\BetterStandardPrinter $betterStandardPrinter, Filesystem $filesystem)
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
        $this->filesystem->dumpFile($filePath, $newContent);
        // @todo how to keep origianl access rights without the SplFileInfo
        // $this->filesystem->chmod($filePath, $fileInfo->getPerms());
        return $newContent;
    }
    public function printParsedStmstAndTokensToString(File $file) : string
    {
        return $this->betterStandardPrinter->printFormatPreserving($file->getNewStmts(), $file->getOldStmts(), $file->getOldTokens());
    }
    public function printParsedStmstAndTokens(File $file) : string
    {
        return $this->printToFile($file->getFilePath(), $file->getNewStmts(), $file->getOldStmts(), $file->getOldTokens());
    }
}
