<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Printer;

use Nette\Utils\Arrays;
use Nette\Utils\Strings;
use PHPStan\PhpDocParser\Ast\Node;
use PHPStan\PhpDocParser\Lexer\Lexer;
use Rector\BetterPhpDocParser\Contract\Doctrine\DoctrineTagNodeInterface;
use Rector\BetterPhpDocParser\ValueObject\StartEndValueObject;

final class OriginalSpacingRestorer
{
    /**
     * @param mixed[] $tokens
     */
    public function restoreInOutputWithTokensStartAndEndPosition(
        Node $node,
        string $nodeOutput,
        array $tokens,
        StartEndValueObject $startEndValueObject
    ): string {
        $oldWhitespaces = $this->detectOldWhitespaces($node, $tokens, $startEndValueObject);

        // no original whitespaces, return
        if ($oldWhitespaces === []) {
            return $nodeOutput;
        }

        $newNodeOutput = '';

        // replace system whitespace by old ones, include \n*
        $nodeOutputParts = Strings::split($nodeOutput, '#\s+#');

        // new nodes were probably added, skip them
        if (count($oldWhitespaces) < count($nodeOutputParts) || count($nodeOutputParts) === 1) {
            return $nodeOutput;
        }

        foreach ($nodeOutputParts as $key => $nodeOutputPart) {
            $newNodeOutput .= $oldWhitespaces[$key] ?? '';
            $newNodeOutput .= $nodeOutputPart;
        }

        // remove first space, added by the printer above
        return Strings::substring($newNodeOutput, 1);
    }

    /**
     * @param mixed[] $tokens
     * @return string[]
     */
    private function detectOldWhitespaces(Node $node, array $tokens, StartEndValueObject $startEndValueObject): array
    {
        $oldWhitespaces = [];

        $start = $startEndValueObject->getStart();
        // this is needed, because of 1 token taken from tokens and added annotation name: "ORM" + "\X" → "ORM\X"
        // todo, this might be needed to be dynamic, based on taken tokens count (some Collector?)
        if ($node instanceof DoctrineTagNodeInterface) {
            --$start;
        }

        for ($i = $start; $i < $startEndValueObject->getEnd(); ++$i) {
            if ($tokens[$i][1] === Lexer::TOKEN_HORIZONTAL_WS) {
                $value = $tokens[$i][0];

                if ($node instanceof DoctrineTagNodeInterface) {
                    // give back "\s+\*" as well
                    if ($i - 1 > $start) { // do not overlap to previous node
                        if (isset($tokens[$i - 1]) && $tokens[$i - 1][1] === Lexer::TOKEN_PHPDOC_EOL) {
                            $previousTokenValue = $tokens[$i - 1][0];
                            if (Strings::match($previousTokenValue, '#\s+\*#m')) {
                                $value = $previousTokenValue . $value;
                            }
                        }
                    }
                }

                $oldWhitespaces[] = $value;
            }

            // quoted string with spaces?
            if ($this->isQuotedStringWithSpaces($tokens, $i)) {
                $matches = Strings::matchAll($tokens[$i][0], '#\s+#m');
                if ($matches !== []) {
                    $oldWhitespaces = array_merge($oldWhitespaces, Arrays::flatten($matches));
                }
            }
        }

        return $oldWhitespaces;
    }

    /**
     * @param mixed[] $tokens
     */
    private function isQuotedStringWithSpaces(array $tokens, int $i): bool
    {
        return in_array(
            $tokens[$i][1],
            [Lexer::TOKEN_SINGLE_QUOTED_STRING, Lexer::TOKEN_DOUBLE_QUOTED_STRING],
            true
        );
    }
}
