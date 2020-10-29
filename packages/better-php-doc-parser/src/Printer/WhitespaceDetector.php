<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Printer;

use Nette\Utils\Arrays;
use Nette\Utils\Strings;
use PHPStan\PhpDocParser\Ast\Node;
use PHPStan\PhpDocParser\Lexer\Lexer;
use Rector\BetterPhpDocParser\Contract\Doctrine\DoctrineTagNodeInterface;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\ShortNameAwareTagInterface;
use Rector\BetterPhpDocParser\ValueObject\StartAndEnd;

final class WhitespaceDetector
{
    /**
     * @var string
     * @see https://regex101.com/r/w7yAt8/1
     */
    private const SPACE_BEFORE_ASTERISK_REGEX = '#\s+\*#m';

    /**
     * @param mixed[] $tokens
     * @return string[]
     */
    public function detectOldWhitespaces(Node $node, array $tokens, StartAndEnd $startAndEnd): array
    {
        $oldWhitespaces = [];

        $start = $startAndEnd->getStart();
        // this is needed, because of 1 token taken from tokens and added annotation name: "ORM" + "\X" → "ORM\X"
        // todo, this might be needed to be dynamic, based on taken tokens count (some Collector?)
        if ($node instanceof DoctrineTagNodeInterface) {
            --$start;
        }

        for ($i = $start; $i < $startAndEnd->getEnd(); ++$i) {
            /** @var string $tokenValue */
            $tokenValue = $tokens[$i][0];

            if ($tokens[$i][1] === Lexer::TOKEN_HORIZONTAL_WS) {
                // give back "\s+\*" as well
                // do not overlap to previous node
                if (($node instanceof DoctrineTagNodeInterface || $node instanceof ShortNameAwareTagInterface) &&
                    $i - 1 > $start &&
                    isset($tokens[$i - 1]) &&
                    $tokens[$i - 1][1] === Lexer::TOKEN_PHPDOC_EOL
                ) {
                    $previousTokenValue = $tokens[$i - 1][0];
                    if (Strings::match($previousTokenValue, self::SPACE_BEFORE_ASTERISK_REGEX)) {
                        $tokenValue = $previousTokenValue . $tokenValue;
                    }
                }

                $oldWhitespaces[] = $tokenValue;
                continue;
            }

            // quoted string with spaces?
            if ($this->isQuotedStringWithSpaces($tokens, $i)) {
                $matches = Strings::matchAll($tokenValue, '#\s+#m');
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
