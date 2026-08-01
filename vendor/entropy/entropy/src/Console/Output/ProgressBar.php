<?php

declare (strict_types=1);
namespace RectorPrefix202608\Entropy\Console\Output;

use RectorPrefix202608\Entropy\Attributes\RelatedTest;
use RectorPrefix202608\Entropy\Tests\Console\Output\ProgressBarTest;
/**
 * Lightweight progress bar rendered on a single, re-written terminal line.
 *
 * The rendering itself is a pure function (@see render()), so it can be unit
 * tested without writing to the terminal.
 *
 * @api used by console applications to report progress
 */
final class ProgressBar
{
    /**
     * @var int
     */
    private const BAR_WIDTH = 28;
    /**
     * @var string
     */
    private const COMPLETE_CHAR = '▓';
    /**
     * @var string
     */
    private const REMAINING_CHAR = '░';
    private int $current = 0;
    private int $maxSteps = 0;
    /**
     * @readonly
     */
    private bool $isSilent;
    public function __construct()
    {
        // avoid printing to stdout during unit tests
        $this->isSilent = defined('PHPUNIT_COMPOSER_INSTALL');
    }
    public function start(int $maxSteps): void
    {
        $this->maxSteps = max(0, $maxSteps);
        $this->current = 0;
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
        if (!$this->isSilent) {
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
        // \r returns the cursor to the line start, so the bar is re-written in place
        fwrite(\STDOUT, "\r" . $this->render());
    }
}
