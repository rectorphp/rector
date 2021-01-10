<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Printer;

use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\PhpdocParserPrinter\Printer\PhpDocPrinter;
use Rector\PhpdocParserPrinter\ValueObject\SmartTokenIterator;

/**
 * @see \Rector\BetterPhpDocParser\Tests\PhpDocInfo\PhpDocInfoPrinter\PhpDocInfoPrinterTest
 */
final class PhpDocInfoPrinter
{
    /**
     * @var EmptyPhpDocDetector
     */
    private $emptyPhpDocDetector;

    /**
     * @var PhpDocPrinter
     */
    private $phpDocPrinter;

    public function __construct(EmptyPhpDocDetector $emptyPhpDocDetector, PhpDocPrinter $phpDocPrinter)
    {
        $this->emptyPhpDocDetector = $emptyPhpDocDetector;
        $this->phpDocPrinter = $phpDocPrinter;
    }

    /**
     * As in php-parser
     *
     * ref: https://github.com/nikic/PHP-Parser/issues/487#issuecomment-375986259
     * - Tokens[node.startPos .. subnode1.startPos]
     * - Print(subnode1)
     * - Tokens[subnode1.endPos .. subnode2.startPos]
     * - Print(subnode2)
     * - Tokens[subnode2.endPos .. node.endPos]
     */
    public function printFormatPreserving(PhpDocInfo $phpDocInfo): string
    {
        if ($phpDocInfo->getTokens() === []) {
            // completely new one, just print string version of it
            if ($phpDocInfo->getPhpDocNode()->children === []) {
                return '';
            }

            return (string) $phpDocInfo->getPhpDocNode();
        }

        $smartTokenIterator = new SmartTokenIterator($phpDocInfo->getTokens());
        return $this->phpDocPrinter->printNode($phpDocInfo->getPhpDocNode(), $smartTokenIterator);
    }
}
