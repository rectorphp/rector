<?php

declare (strict_types=1);
namespace RectorPrefix202609\Entropy\Console\Output;

use RectorPrefix202609\Entropy\Attributes\RelatedTest;
use RectorPrefix202609\Entropy\Console\Enum\Color;
use RectorPrefix202609\Entropy\Tests\Console\Output\OutputColozierTest;
final class OutputColorizer
{
    /**
     * @readonly
     */
    private bool $useColors;
    public function __construct()
    {
        if (defined('PHPUNIT_COMPOSER_INSTALL')) {
            // enable colors during unit tests
            $this->useColors = \true;
            return;
        }
        $this->useColors = $this->isTty();
    }
    /**
     * @api used in tests
     */
    public function colorize(string $text): string
    {
        // foreground colors: <fg=green>text</>
        if (preg_match_all('#<fg=(green|yellow|red|cyan)>(.*?)</>#su', $text, $matches, \PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $text = str_replace($match[0], $this->color($match[2], $match[1]), $text);
            }
        }
        // background colors: <bg=green>text</>
        if (preg_match_all('#<bg=(green|yellow|red|cyan)>(.*?)</>#su', $text, $matches, \PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $content = $match[2];
                $color = $match[1];
                $text = str_replace($match[0], $this->background($content, $color), $text);
            }
        }
        return $text;
    }
    /**
     * @param Color::* $color
     */
    public function color(string $text, string $color): string
    {
        if (!$this->useColors) {
            return $text;
        }
        switch ($color) {
            case Color::GREEN:
                return "\x1b[32m{$text}\x1b[0m";
            case Color::YELLOW:
                return "\x1b[33m{$text}\x1b[0m";
            case Color::RED:
                return "\x1b[31m{$text}\x1b[0m";
            case Color::CYAN:
                return "\x1b[36m{$text}\x1b[0m";
            case Color::GREY:
                return "\x1b[37m{$text}\x1b[0m";
        }
    }
    /**
     * @param Color::* $color
     */
    public function background(string $text, string $color): string
    {
        $text = $this->padding($text);
        if (!$this->useColors) {
            return $text;
        }
        switch ($color) {
            case Color::GREEN:
                return "\x1b[42;30m{$text}\x1b[0m";
            case Color::YELLOW:
            case 'orange':
                return "\x1b[43;30m{$text}\x1b[0m";
            case Color::RED:
                return "\x1b[41;30m{$text}\x1b[0m";
            case Color::CYAN:
                return "\x1b[46;30m{$text}\x1b[0m";
        }
    }
    private function padding(string $text): string
    {
        return ' ' . $text . ' ';
    }
    private function isTty(): bool
    {
        if (function_exists('stream_isatty')) {
            return @stream_isatty(\STDOUT);
        }
        // Fallback: respect NO_COLOR if present
        return getenv('NO_COLOR') === \false;
    }
}
