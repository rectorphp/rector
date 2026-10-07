<?php

declare (strict_types=1);
namespace RectorPrefix202610\Entropy\Console\Output;

use RectorPrefix202610\Entropy\Attribute\RelatedTest;
use RectorPrefix202610\Entropy\Tests\Console\Output\ProgressBarTest;
/**
 * Lightweight progress bar rendered on a single, re-written terminal line.
 *
 * The rendering itself is a pure function (@see render()), so it can be unit
 * tested without writing to the terminal.
 *
 * @api used by console applications to report progress
 * @see \Entropy\Tests\Console\Output\ProgressBarTest
 */
final class ProgressBar
{
    private const BAR_WIDTH = 28;
    private const COMPLETE_CHAR = '▓';
    private const REMAINING_CHAR = '░';
    // in non-interactive output (CI) print a line only every N percent, to avoid spamming the log
    private const CI_PERCENT_STEP = 20;
    private int $current = 0;
    private int $maxSteps = 0;
    private bool $isSilent;
    private bool $isDecorated;
    private int $lastPrintedPercent = -self::CI_PERCENT_STEP;
    public function __construct()
    {
        // avoid printing to stdout during unit tests
        $this->isSilent = defined('PHPUNIT_COMPOSER_INSTALL');
        // a non-interactive stream (CI, piped output) cannot rewrite a line, so fall back to milestone lines
        $this->isDecorated = !$this->isSilent && stream_isatty(\STDOUT);
    }
    public function start(int $maxSteps): void
    {
        $this->maxSteps = max(0, $maxSteps);
        $this->current = 0;
        $this->lastPrintedPercent = -self::CI_PERCENT_STEP;
        $this->display();
    }
    public function advance(int $step = 1): void
    {
        $this->setProgress($this->current + $step);
    }
    public function setProgress(int $current): void
    {
        $this->current = max(0, min($current, $this->maxSteps));
        $this->display();
    }
    public function finish(): void
    {
        $this->current = $this->maxSteps;
        $this->display();
        // only the in-place bar needs a closing newline; milestone lines already end with one
        if (!$this->isSilent && $this->isDecorated) {
            fwrite(\STDOUT, \PHP_EOL);
        }
    }
    /**
     * Pure rendering of the current state, e.g. " 5/10 [▓▓▓▓▓▓▓▓▓▓▓▓▓▓░░░░░░░░░░░░░░]  50%"
     */
    public function render(): string
    {
        $percent = $this->resolvePercent();
        $completeWidth = (int) floor($percent * self::BAR_WIDTH);
        $bar = str_repeat(self::COMPLETE_CHAR, $completeWidth) . str_repeat(self::REMAINING_CHAR, max(0, self::BAR_WIDTH - $completeWidth));
        return sprintf('%d/%d [%s] %3d%%', $this->current, $this->maxSteps, $bar, (int) round($percent * 100));
    }
    private function resolvePercent(): float
    {
        if ($this->maxSteps === 0) {
            return 1.0;
        }
        return $this->current / $this->maxSteps;
    }
    private function display(): void
    {
        if ($this->isSilent) {
            return;
        }
        if ($this->isDecorated) {
            // \r returns the cursor to the line start, so the bar is re-written in place
            fwrite(\STDOUT, "\r" . $this->render());
            return;
        }
        // CI: print a standalone line only when a new percent milestone is reached
        $percent = (int) round($this->resolvePercent() * 100);
        $isMilestone = $percent >= $this->lastPrintedPercent + self::CI_PERCENT_STEP;
        $isFinalStep = $percent === 100 && $this->lastPrintedPercent !== 100;
        if (!$isMilestone && !$isFinalStep) {
            return;
        }
        $this->lastPrintedPercent = $percent;
        fwrite(\STDOUT, $this->render() . \PHP_EOL);
    }
}
