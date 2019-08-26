<?php declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Printer;

use Nette\Utils\Arrays;
use Nette\Utils\Strings;
use PHPStan\PhpDocParser\Ast\Node;
use PHPStan\PhpDocParser\Lexer\Lexer;
use Rector\BetterPhpDocParser\Data\StartEndInfo;
use Rector\DoctrinePhpDocParser\Contract\Ast\PhpDoc\DoctrineTagNodeInterface;

final class OriginalSpacingRestorer
{
    /**
     * @param mixed[] $tokens
     */
    public function restoreInOutputWithTokensStartAndEndPosition(
        Node $node,
        string $nodeOutput,
        array $tokens,
        StartEndInfo $startEndInfo
    ): string {
        $oldWhitespaces = $this->detectOldWhitespaces($node, $tokens, $startEndInfo);

        // no original whitespaces, return
        if ($oldWhitespaces === []) {
            return $nodeOutput;
        }

        $newNodeOutput = '';
        $i = 0;
        // replace system whitespace by old ones
        foreach (Strings::split($nodeOutput, '#\s+#') as $nodeOutputPart) {
            $newNodeOutput .= ($oldWhitespaces[$i] ?? '') . $nodeOutputPart;
            ++$i;
        }

        // remove first space, added by the printer above
        return Strings::substring($newNodeOutput, 1);
    }

    /**
     * @param mixed[] $tokens
     * @return string[]
     */
    private function detectOldWhitespaces(Node $node, array $tokens, StartEndInfo $startEndInfo): array
    {
        $oldWhitespaces = [];

        $start = $startEndInfo->getStart();
        if ($node instanceof DoctrineTagNodeInterface) {
            --$start;
        }

        for ($i = $start; $i < $startEndInfo->getEnd(); ++$i) {
            if ($tokens[$i][1] === Lexer::TOKEN_HORIZONTAL_WS) {
                $oldWhitespaces[] = $tokens[$i][0];
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
