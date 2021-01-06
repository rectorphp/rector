<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Printer;

use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\ValueObject\StartAndEnd;
use Rector\PhpdocParserPrinter\Printer\PhpDocPrinter;
use Rector\PhpdocParserPrinter\ValueObject\PhpDocNode\AttributeAwarePhpDocNode;
use Rector\PhpdocParserPrinter\ValueObject\SmartTokenIterator;

/**
 * @see \Rector\BetterPhpDocParser\Tests\PhpDocInfo\PhpDocInfoPrinter\PhpDocInfoPrinterTest
 */
final class PhpDocInfoPrinter
{
    /**
     * @var string
     * @see https://regex101.com/r/5fJyws/1
     */
    private const CALLABLE_REGEX = '#callable(\s+)\(#';

    /**
     * @var int
     */
    private $tokenCount;

    /**
     * @var int
     */
    private $currentTokenPosition;

    /**
     * @var mixed[]
     */
    private $tokens = [];

    /**
     * @var StartAndEnd[]
     */
    private $removedNodePositions = [];

    /**
     * @var AttributeAwarePhpDocNode
     */
    private $attributeAwarePhpDocNode;

    /**
     * @var PhpDocInfo
     */
    private $phpDocInfo;

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
