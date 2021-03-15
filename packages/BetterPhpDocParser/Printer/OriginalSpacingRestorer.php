<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Printer;

use Nette\Utils\Strings;
use PHPStan\PhpDocParser\Ast\Node;
use Rector\BetterPhpDocParser\ValueObject\StartAndEnd;

final class OriginalSpacingRestorer
{
    /**
     * @var string
     * @see https://regex101.com/r/TMk388/1
     */
    public const WHITESPACE_SPLIT_REGEX = '#\s+(\*)?#';

    /**
     * @var WhitespaceDetector
     */
    private $whitespaceDetector;

    public function __construct(WhitespaceDetector $whitespaceDetector)
    {
        $this->whitespaceDetector = $whitespaceDetector;
    }

    /**
     * @param mixed[] $tokens
     */
    public function restoreInOutputWithTokensStartAndEndPosition(
        Node $node,
        string $nodeOutput,
        array $tokens,
        StartAndEnd $startAndEnd
    ): string {
        $oldWhitespaces = $this->whitespaceDetector->detectOldWhitespaces($node, $tokens, $startAndEnd);

        // no original whitespaces, return
        if ($oldWhitespaces === []) {
            return $nodeOutput;
        }

        $newNodeOutput = '';

        // replace system whitespace by old ones, include \n*
        $nodeOutputParts = Strings::split($nodeOutput, self::WHITESPACE_SPLIT_REGEX);

        $oldWhitespaceCount = count($oldWhitespaces);
        $nodeOutputPartCount = count($nodeOutputParts);

        // new nodes were probably added, skip them
        if ($nodeOutputPartCount === 1) {
            return $nodeOutput;
        }

        if ($oldWhitespaceCount < $nodeOutputPartCount - 1) {
            return $nodeOutput;
        }

        $asteriskSpaceFirst = Strings::contains($oldWhitespaces[0], '*');

        foreach ($nodeOutputParts as $key => $nodeOutputPart) {
            if ($asteriskSpaceFirst) {
                $newNodeOutput .= $nodeOutputPart;
                $newNodeOutput .= $oldWhitespaces[$key] ?? ' ';
            } else {
                $newNodeOutput .= $oldWhitespaces[$key] ?? ' ';
                $newNodeOutput .= $nodeOutputPart;
            }
        }

        if ($asteriskSpaceFirst) {
            return rtrim($newNodeOutput);
        }

        // remove first space, added by the printer above
        return Strings::substring($newNodeOutput, 1);
    }
}
