<?php

declare (strict_types=1);
namespace Rector\Agentic;

/**
 * Detects whether Rector runs in an interactive terminal, so non-interactive callers - pipes, CI,
 * agents - get clean machine output instead of human chrome (progress bar, ANSI, prompts).
 */
final class TerminalDetector
{
    public static function isOutputTty(): bool
    {
        return defined('STDOUT') && stream_isatty(\STDOUT);
    }
    public static function isInputTty(): bool
    {
        return defined('STDIN') && stream_isatty(\STDIN);
    }
}
