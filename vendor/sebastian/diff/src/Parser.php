<?php

declare (strict_types=1);
/*
 * This file is part of sebastian/diff.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix202608\SebastianBergmann\Diff;

use const PREG_UNMATCHED_AS_NULL;
use function array_pop;
use function count;
use function max;
use function preg_match;
use function preg_split;
/**
 * Unified diff parser.
 */
final class Parser
{
    /**
     * @return list<Diff>
     */
    public function parse(string $string): array
    {
        $lines = preg_split('(\r\n|\r|\n)', $string);
        if ($lines === \false) {
            return [];
        }
        if ($lines !== [] && $lines[count($lines) - 1] === '') {
            array_pop($lines);
        }
        $lineCount = count($lines);
        $diffs = [];
        $diff = null;
        $collected = [];
        for ($i = 0; $i < $lineCount; $i++) {
            if (!isset($lines[$i])) {
                continue;
            }
            $line = $lines[$i];
            $nextLine = $lines[$i + 1] ?? null;
            if ($nextLine !== null && preg_match('#^---\h+"?(?P<file>[^\v\t"]+)#', $line, $fromMatch) === 1 && preg_match('#^\+\+\+\h+"?(?P<file>[^\v\t"]+)#', $nextLine, $toMatch) === 1) {
                if ($diff !== null) {
                    $this->parseFileDiff($diff, $collected);
                    $diffs[] = $diff;
                    $collected = [];
                }
                $diff = new Diff($fromMatch['file'], $toMatch['file']);
                $i++;
                continue;
            }
            if (preg_match('/^(?:diff --git |index [\da-f.]+|[+-]{3} [ab])/', $line) === 1) {
                continue;
            }
            $collected[] = $line;
        }
        if ($diff !== null && $collected !== []) {
            $this->parseFileDiff($diff, $collected);
            $diffs[] = $diff;
        }
        return $diffs;
    }
    /**
     * @param string[] $lines
     */
    private function parseFileDiff(Diff $diff, array $lines): void
    {
        $chunks = [];
        $chunk = null;
        $diffLines = [];
        foreach ($lines as $line) {
            if (preg_match('/^@@\s+-(?P<start>\d+)(?:,\s*(?P<startrange>\d+))?\s+\+(?P<end>\d+)(?:,\s*(?P<endrange>\d+))?\s+@@/', $line, $match, PREG_UNMATCHED_AS_NULL) === 1) {
                $chunk = new Chunk((int) $match['start'], isset($match['startrange']) ? max(0, (int) $match['startrange']) : 1, (int) $match['end'], isset($match['endrange']) ? max(0, (int) $match['endrange']) : 1);
                $chunks[] = $chunk;
                $diffLines = [];
                continue;
            }
            if (preg_match('/^(?P<type>[+ -])?(?P<line>.*)/', $line, $match) === 1) {
                $type = Line::UNCHANGED;
                if ($match['type'] === '+') {
                    $type = Line::ADDED;
                } elseif ($match['type'] === '-') {
                    $type = Line::REMOVED;
                }
                $diffLines[] = new Line($type, $match['line']);
                ($nullsafeVariable1 = $chunk) ? $nullsafeVariable1->setLines($diffLines) : null;
            }
        }
        $diff->setChunks($chunks);
    }
}
