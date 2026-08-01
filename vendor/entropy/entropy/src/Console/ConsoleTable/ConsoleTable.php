<?php

declare (strict_types=1);
namespace RectorPrefix202608\Entropy\Console\ConsoleTable;

use RectorPrefix202608\Entropy\Attributes\RelatedTest;
use RectorPrefix202608\Entropy\Console\Output\OutputPrinter;
use RectorPrefix202608\Entropy\Tests\Console\ConsoleTable\ConsoleTableTest;
final class ConsoleTable
{
    /**
     * @readonly
     */
    private OutputPrinter $outputPrinter;
    /**
     * @api used in tests
     * Marks a separator line between table rows.
     * @var string
     */
    public const SEPARATOR = '__separator__';
    /**
     * @var int
     */
    private const COLUMN_PADDING = 2;
    public function __construct(OutputPrinter $outputPrinter)
    {
        $this->outputPrinter = $outputPrinter;
    }
    /**
     * @param string[] $headers
     * @param array<string[]|self::SEPARATOR> $rows
     */
    public function render(array $headers, array $rows, int $minWidth = 0): void
    {
        foreach ($this->createTableLines($headers, $rows, $minWidth) as $line) {
            $this->outputPrinter->writeln($line);
        }
    }
    /**
     * Pure rendering of the table into aligned text lines, so it can be unit tested.
     *
     * @param string[] $headers
     * @param array<string[]|self::SEPARATOR> $rows
     * @return string[]
     */
    public function createTableLines(array $headers, array $rows, int $minWidth = 0): array
    {
        $columnWidths = $this->resolveColumnWidths($headers, $rows);
        $columnWidths = $this->expandToMinWidth($columnWidths, $minWidth);
        $coloredHeaders = array_map(static fn(string $header): string => '<fg=green>' . $header . '</>', $headers);
        $borderLine = $this->createBorderLine($columnWidths);
        $lines = [];
        $lines[] = $borderLine;
        $lines[] = $this->formatRow($coloredHeaders, $columnWidths);
        $lines[] = $borderLine;
        foreach ($rows as $row) {
            if ($row === self::SEPARATOR) {
                $lines[] = $borderLine;
                continue;
            }
            $lines[] = $this->formatRow($row, $columnWidths);
        }
        $lines[] = $borderLine;
        return $lines;
    }
    /**
     * @param string[] $headers
     * @param array<string[]|self::SEPARATOR> $rows
     * @return int[]
     */
    private function resolveColumnWidths(array $headers, array $rows): array
    {
        $columnWidths = [];
        foreach ($headers as $columnIndex => $header) {
            $columnWidths[$columnIndex] = $this->visibleLength($header);
        }
        foreach ($rows as $row) {
            if ($row === self::SEPARATOR) {
                continue;
            }
            foreach ($row as $columnIndex => $cell) {
                $columnWidths[$columnIndex] = max($columnWidths[$columnIndex] ?? 0, $this->visibleLength($cell));
            }
        }
        return $columnWidths;
    }
    /**
     * Grows the first column so the whole table spans at least the given width.
     *
     * @param int[] $columnWidths
     * @return int[]
     */
    private function expandToMinWidth(array $columnWidths, int $minWidth): array
    {
        $totalWidth = $this->resolveTotalWidth($columnWidths);
        if ($totalWidth < $minWidth) {
            $columnWidths[0] += $minWidth - $totalWidth;
        }
        return $columnWidths;
    }
    /**
     * Total rendered width: each column takes "  cell " (2 leading + content + 1 trailing space).
     *
     * @param int[] $columnWidths
     */
    private function resolveTotalWidth(array $columnWidths): int
    {
        return array_sum($columnWidths) + count($columnWidths) * (self::COLUMN_PADDING + 1);
    }
    /**
     * Renders a single row as " cell  cell  cell", each cell padded to its column width.
     *
     * @param string[] $cells
     * @param int[] $columnWidths
     */
    private function formatRow(array $cells, array $columnWidths): string
    {
        $line = '';
        foreach ($cells as $columnIndex => $cell) {
            $padding = $columnWidths[$columnIndex] - $this->visibleLength($cell);
            $line .= '  ' . $cell . str_repeat(' ', $padding) . ' ';
        }
        return rtrim($line);
    }
    /**
     * Renders a border line as " ----- ----- -----", one dash run per column.
     *
     * @param int[] $columnWidths
     */
    private function createBorderLine(array $columnWidths): string
    {
        $segments = [];
        foreach ($columnWidths as $columnWidth) {
            $segments[] = str_repeat('-', $columnWidth + self::COLUMN_PADDING);
        }
        return ' ' . implode(' ', $segments);
    }
    /**
     * Length of the text as rendered, ignoring color tags like "<fg=green>".
     */
    private function visibleLength(string $text): int
    {
        $stripped = preg_replace('#</?>|<(?:fg|bg)=(?:green|yellow|red|cyan|orange|grey)>#', '', $text);
        return strlen($stripped ?? $text);
    }
}
