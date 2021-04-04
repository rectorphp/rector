<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Printer;

use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use PHPStan\PhpDocParser\Lexer\Lexer;
use Rector\BetterPhpDocParser\Attributes\Attribute\Attribute;
use Rector\BetterPhpDocParser\ValueObject\Parser\BetterTokenIterator;
use Rector\BetterPhpDocParser\ValueObject\StartAndEnd;

final class RemoveTokensPositionResolver
{
    /**
     * @return StartAndEnd[]
     */
    public function resolve(
        BetterTokenIterator $betterTokenIterator,
        PhpDocNode $originalPhpDocNode,
        PhpDocNode $currentPhpDocNode
    ): array {
        $removedNodePositions = [];

        $removedNodes = array_diff($originalPhpDocNode->children, $currentPhpDocNode->children);

        $lastEndPosition = null;
        $tokens = $betterTokenIterator->getTokens();

        foreach ($removedNodes as $removedNode) {
            /** @var StartAndEnd $removedPhpDocNodeInfo */
            $removedPhpDocNodeInfo = $removedNode->getAttribute(Attribute::START_END);

            // change start position to start of the line, so the whole line is removed
            $seekPosition = $removedPhpDocNodeInfo->getStart();

            while ($seekPosition >= 0 && $tokens[$seekPosition][1] !== Lexer::TOKEN_HORIZONTAL_WS) {
                if ($tokens[$seekPosition][1] === Lexer::TOKEN_PHPDOC_EOL) {
                    break;
                }

                // do not colide
                if ($lastEndPosition < $seekPosition) {
                    break;
                }

                --$seekPosition;
            }

            $lastEndPosition = $removedPhpDocNodeInfo->getEnd();

            $removedNodePositions[] = new StartAndEnd(max(0, $seekPosition - 1), $removedPhpDocNodeInfo->getEnd());
        }

        return $removedNodePositions;
    }
}
