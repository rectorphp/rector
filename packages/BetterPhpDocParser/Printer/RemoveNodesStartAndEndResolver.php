<?php

declare (strict_types=1);
namespace Rector\BetterPhpDocParser\Printer;

use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocChildNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use PHPStan\PhpDocParser\Lexer\Lexer;
use Rector\BetterPhpDocParser\ValueObject\PhpDocAttributeKey;
use Rector\BetterPhpDocParser\ValueObject\StartAndEnd;
final class RemoveNodesStartAndEndResolver
{
    /**
     * @param mixed[] $tokens
     * @return StartAndEnd[]
     */
    public function resolve(\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode $originalPhpDocNode, \PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode $currentPhpDocNode, array $tokens) : array
    {
        $removedNodePositions = [];
        /** @var PhpDocChildNode[] $removedChildNodes */
        $removedChildNodes = \array_diff($originalPhpDocNode->children, $currentPhpDocNode->children);
        $lastEndPosition = null;
        foreach ($removedChildNodes as $removedChildNode) {
            /** @var StartAndEnd $removedPhpDocNodeInfo */
            $removedPhpDocNodeInfo = $removedChildNode->getAttribute(\Rector\BetterPhpDocParser\ValueObject\PhpDocAttributeKey::START_AND_END);
            // change start position to start of the line, so the whole line is removed
            $seekPosition = $removedPhpDocNodeInfo->getStart();
            while ($seekPosition >= 0 && $tokens[$seekPosition][1] !== \PHPStan\PhpDocParser\Lexer\Lexer::TOKEN_HORIZONTAL_WS) {
                if ($tokens[$seekPosition][1] === \PHPStan\PhpDocParser\Lexer\Lexer::TOKEN_PHPDOC_EOL) {
                    break;
                }
                // do not colide
                if ($lastEndPosition < $seekPosition) {
                    break;
                }
                --$seekPosition;
            }
            $lastEndPosition = $removedPhpDocNodeInfo->getEnd();
            $removedNodePositions[] = new \Rector\BetterPhpDocParser\ValueObject\StartAndEnd(\max(0, $seekPosition - 1), $removedPhpDocNodeInfo->getEnd());
        }
        return $removedNodePositions;
    }
}
