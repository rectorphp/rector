<?php

declare (strict_types=1);
namespace RectorPrefix202608\Entropy\Console\Terminal;

final class Terminal
{
    /**
     * @see SymfonyStyle::MAX_LINE_LENGTH
     * @var int
     */
    private const MAX_LINE_LENGTH = 120;
    /**
     * Detect terminal width, capped at MAX_LINE_LENGTH, the same way SymfonyStyle does.
     */
    public static function getWidth(): int
    {
        $columns = getenv('COLUMNS');
        if ($columns !== \false && is_numeric($columns)) {
            return min((int) $columns, self::MAX_LINE_LENGTH);
        }
        $sttySize = @exec('stty size 2>/dev/null');
        if (is_string($sttySize) && preg_match('#\d+ (?<columns>\d+)#', $sttySize, $matches) === 1) {
            return min((int) $matches['columns'], self::MAX_LINE_LENGTH);
        }
        return self::MAX_LINE_LENGTH;
    }
    public static function padVisibleRight(string $text, int $width, string $padChar = ' '): string
    {
        $len = self::visibleLength($text);
        if ($len >= $width) {
            return $text;
        }
        return $text . str_repeat($padChar, $width - $len);
    }
    private static function visibleLength(string $text): int
    {
        // remove console meta tags like <fg=green> ... </> and <bg=red> ... </>
        $stripped = preg_replace('#</?>|<(?:fg|bg)=(?:green|yellow|red|cyan|orange)>#', '', $text);
        $stripped ??= $text;
        return strlen($stripped);
    }
}
