<?php

declare(strict_types=1);

namespace Rector\Core\PhpParser\Printer;

use Nette\Utils\FileSystem;
use PhpParser\Node;
use Rector\Core\ValueObject\Application\ParsedStmtsAndTokens;
use Symplify\SmartFileSystem\SmartFileInfo;

/**
 * @see \Rector\Core\Tests\PhpParser\Printer\FormatPerservingPrinterTest
 */
final class FormatPerservingPrinter
{
    /**
     * @var BetterStandardPrinter
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

        FileSystem::write($fileInfo->getRealPath(), $newContent, $fileInfo->getPerms());

        return $newContent;
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

    public function printParsedStmstAndTokensToString(ParsedStmtsAndTokens $parsedStmtsAndTokens): string
    {
        return $this->betterStandardPrinter->printFormatPreserving($parsedStmtsAndTokens->getNewStmts(),
            $parsedStmtsAndTokens->getOldStmts(), $parsedStmtsAndTokens->getOldTokens());
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
}
