<?php

declare (strict_types=1);
namespace Rector\Console\Formatter;

use RectorPrefix202510\Nette\Utils\Strings;
use Rector\Util\NewLineSplitter;
use RectorPrefix202510\Symfony\Component\Console\Formatter\OutputFormatter;
/**
 * Inspired by @see https://github.com/FriendsOfPHP/PHP-CS-Fixer/blob/master/src/Differ/DiffConsoleFormatter.php to be
 * used as standalone class, without need to require whole package by Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * @see \Rector\Tests\Console\Formatter\ColorConsoleDiffFormatterTest
 */
final class ColorConsoleDiffFormatter
{
    /**
     * @var string
     * @see https://regex101.com/r/ovLMDF/1
     */
    private const PLUS_START_REGEX = '#^(\+.*)#';
    /**
     * @var string
     * @see https://regex101.com/r/xwywpa/1
     */
    private const MINUS_START_REGEX = '#^(\-.*)#';
    /**
     * @var string
     * @see https://regex101.com/r/CMlwa8/1
     */
    private const AT_START_REGEX = '#^(@.*)#';
    /**
     * @var string
     * @see https://regex101.com/r/8MXnfa/2
     */
    private const AT_DIFF_LINE_REGEX = '#^\<fg=cyan\>@@ \-\d+,\d+ \+\d+,\d+ @@\<\/fg=cyan\>$#';
    /**
     * @readonly
     */
    private string $template;
    public function __construct()
    {
        $this->template = sprintf('<comment>    ---------- begin diff ----------</comment>%s%%s%s<comment>    ----------- end diff -----------</comment>' . \PHP_EOL, \PHP_EOL, \PHP_EOL);
    }
    public function format(string $diff): string
    {
        return $this->formatWithTemplate($diff, $this->template);
    }
    private function formatWithTemplate(string $diff, string $template): string
    {
        $escapedDiff = OutputFormatter::escape(rtrim($diff));
        $escapedDiffLines = NewLineSplitter::split($escapedDiff);
        // remove description of added + remove, obvious on diffs
        // decorize lines
        foreach ($escapedDiffLines as $key => $escapedDiffLine) {
            if ($escapedDiffLine === '--- Original') {
                unset($escapedDiffLines[$key]);
                continue;
            }
            if ($escapedDiffLine === '+++ New') {
                unset($escapedDiffLines[$key]);
                continue;
            }
            if ($escapedDiffLine === ' ') {
                $escapedDiffLines[$key] = '';
                continue;
            }
            $escapedDiffLine = $this->makePlusLinesGreen($escapedDiffLine);
            $escapedDiffLine = $this->makeMinusLinesRed($escapedDiffLine);
            $escapedDiffLine = $this->makeAtNoteCyan($escapedDiffLine);
            $escapedDiffLine = $this->normalizeLineAtDiff($escapedDiffLine);
            // final decorized line
            $escapedDiffLines[$key] = $escapedDiffLine;
        }
        return sprintf($template, implode(\PHP_EOL, $escapedDiffLines));
    }
    /**
     * Remove number diff, eg; @@ -67,6 +67,8 @@ to become @@ @@
     */
    private function normalizeLineAtDiff(string $string): string
    {
        return Strings::replace($string, self::AT_DIFF_LINE_REGEX, '<fg=cyan>@@ @@</fg=cyan>');
    }
    private function makePlusLinesGreen(string $string): string
    {
        return Strings::replace($string, self::PLUS_START_REGEX, '<fg=green>$1</fg=green>');
    }
    private function makeMinusLinesRed(string $string): string
    {
        return Strings::replace($string, self::MINUS_START_REGEX, '<fg=red>$1</fg=red>');
    }
    private function makeAtNoteCyan(string $string): string
    {
        return Strings::replace($string, self::AT_START_REGEX, '<fg=cyan>$1</fg=cyan>');
    }
}
