<?php

declare (strict_types=1);
namespace RectorPrefix202609\Entropy\Console\Output;

use RectorPrefix202609\Entropy\Console\Enum\Color;
use RectorPrefix202609\Entropy\Console\Terminal\Terminal;
use RectorPrefix202609\Webmozart\Assert\Assert;
/**
 * @api used in many ways
 */
final class OutputPrinter
{
    /**
     * @readonly
     */
    private OutputColorizer $outputColorizer;
    /**
     * @readonly
     */
    private bool $isSilent;
    public function __construct(OutputColorizer $outputColorizer)
    {
        $this->outputColorizer = $outputColorizer;
        // avoid printing to stdout during unit tests
        $this->isSilent = defined('PHPUNIT_COMPOSER_INSTALL');
    }
    /**
     * Handle color background and foreground tags in the text
     * e.g. <fg=green>text</>, <bg=red>text</>
     */
    public function writeln(string $text, int $newlineCount = 0): void
    {
        if ($this->isSilent) {
            return;
        }
        $coloredText = $this->outputColorizer->colorize($text);
        fwrite(\STDOUT, $coloredText . \PHP_EOL);
        if ($newlineCount !== 0) {
            $this->newline($newlineCount);
        }
    }
    public function yellow(string $text): void
    {
        $colorizedText = $this->outputColorizer->color($text, 'yellow');
        $this->writeln($colorizedText);
    }
    public function green(string $text): void
    {
        $colorizedText = $this->outputColorizer->color($text, 'green');
        $this->writeln($colorizedText);
    }
    public function orangeBackground(string $text): void
    {
        $this->writeln($this->outputColorizer->background($text, Color::YELLOW));
    }
    public function greenBackground(string $text): void
    {
        $this->writeln($this->outputColorizer->background($text, Color::GREEN));
    }
    public function redBackground(string $text): void
    {
        $this->writeln($this->outputColorizer->background($text, Color::RED));
    }
    public function newline(int $count = 1): void
    {
        if ($this->isSilent) {
            return;
        }
        fwrite(\STDOUT, str_repeat(\PHP_EOL, $count));
    }
    /**
     * @param string[] $items
     */
    public function listing(array $items, string $bulletChar = '*'): void
    {
        Assert::allString($items);
        foreach ($items as $item) {
            $this->writeln(sprintf('%s %s', $bulletChar, $item));
        }
    }
    public function title(string $text): void
    {
        $this->newline();
        $this->yellow($text);
        $this->yellow(str_repeat('=', strlen($text)));
        $this->newline();
    }
    public function section(string $text): void
    {
        $this->newline();
        $this->yellow($text);
        $this->yellow(str_repeat('-', strlen($text)));
    }
    public function success(string $text): void
    {
        $this->block('[OK] ' . $text, Color::GREEN);
    }
    public function warning(string $text): void
    {
        $this->block('[WARNING] ' . $text, Color::YELLOW);
    }
    public function error(string $text): void
    {
        $this->block('[ERROR] ' . $text, Color::RED);
    }
    /**
     * Ask the user a question and return the trimmed answer, or the default when nothing is entered.
     */
    public function ask(string $question, ?string $default = null): ?string
    {
        $suffix = $default !== null ? sprintf(' [%s]', $default) : '';
        $this->writeln($this->outputColorizer->color($question . $suffix . ':', Color::YELLOW));
        if ($this->isSilent) {
            return $default;
        }
        $answer = fgets(\STDIN);
        if ($answer === \false) {
            return $default;
        }
        $answer = trim($answer);
        return $answer === '' ? $default : $answer;
    }
    /**
     * Print a colored block padded by a blank colored line above and below the message,
     * the same way SymfonyStyle renders success/warning/error blocks.
     *
     * @param Color::* $color
     */
    private function block(string $text, string $color): void
    {
        // reserve 2 chars for the single space padding the background() adds on each side
        $contentWidth = Terminal::getWidth() - 2;
        $emptyLine = str_repeat(' ', $contentWidth);
        $paddedText = Terminal::padVisibleRight($text, $contentWidth);
        $this->newline();
        $this->writeln($this->outputColorizer->background($emptyLine, $color));
        $this->writeln($this->outputColorizer->background($paddedText, $color));
        $this->writeln($this->outputColorizer->background($emptyLine, $color));
        $this->newline();
    }
}
