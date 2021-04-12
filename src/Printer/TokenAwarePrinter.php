<?php

declare(strict_types=1);

namespace Rector\Core\Printer;

use Rector\Core\Application\TokensByFilePathStorage;
use Rector\Core\PhpParser\Printer\FormatPerservingPrinter;
use Symplify\SmartFileSystem\SmartFileInfo;

final class TokenAwarePrinter
{
    /**
     * @var TokensByFilePathStorage
     */
    private $tokensByFilePathStorage;

    /**
     * @var FormatPerservingPrinter
     */
    private $formatPerservingPrinter;

    public function __construct(
        TokensByFilePathStorage $tokensByFilePathStorage,
        FormatPerservingPrinter $formatPerservingPrinter
    ) {
        $this->tokensByFilePathStorage = $tokensByFilePathStorage;
        $this->formatPerservingPrinter = $formatPerservingPrinter;
    }

    /**
     * See https://github.com/nikic/PHP-Parser/issues/344#issuecomment-298162516.
     */
    public function printToString(SmartFileInfo $smartFileInfo): string
    {
        $parsedStmtsAndTokens = $this->tokensByFilePathStorage->getForFileInfo($smartFileInfo);
        return $this->formatPerservingPrinter->printParsedStmstAndTokensToString($parsedStmtsAndTokens);
    }

    public function printToFile(SmartFileInfo $smartFileInfo): string
    {
        $parsedStmtsAndTokens = $this->tokensByFilePathStorage->getForFileInfo($smartFileInfo);
        return $this->formatPerservingPrinter->printParsedStmstAndTokens($smartFileInfo, $parsedStmtsAndTokens);
    }
}
