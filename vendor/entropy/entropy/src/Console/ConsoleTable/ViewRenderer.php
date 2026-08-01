<?php

declare (strict_types=1);
namespace RectorPrefix202608\Entropy\Console\ConsoleTable;

use RectorPrefix202608\Entropy\Attributes\RelatedTest;
use RectorPrefix202608\Entropy\Console\ConsoleTable\ValueObject\TableRow;
use RectorPrefix202608\Entropy\Console\ConsoleTable\ValueObject\TableView;
use RectorPrefix202608\Entropy\Console\Output\OutputPrinter;
use RectorPrefix202608\Entropy\Tests\Console\ConsoleTable\ViewRendererTest;
final class ViewRenderer
{
    /**
     * @readonly
     */
    private OutputPrinter $outputPrinter;
    /**
     * @readonly
     */
    private ConsoleTable $consoleTable;
    /**
     * @var int Tables span at least this many characters wide
     */
    private const MIN_WIDTH = 60;
    public function __construct(OutputPrinter $outputPrinter, ConsoleTable $consoleTable)
    {
        $this->outputPrinter = $outputPrinter;
        $this->consoleTable = $consoleTable;
    }
    /**
     * @api to be used outside
     */
    public function renderTableView(TableView $tableView): void
    {
        $this->outputPrinter->newline();
        $headers = [$tableView->getTitle(), $tableView->getLabel()];
        if ($tableView->isShouldIncludeRelative()) {
            $headers[] = 'Relative';
        }
        $countColumnWidth = $this->resolveCountColumnWidth($tableView);
        $percentColumnWidth = $this->resolvePercentColumnWidth($tableView);
        $rows = [];
        foreach ($tableView->getRows() as $tableRow) {
            $rows[] = $this->createRow($tableRow, $tableView, $countColumnWidth, $percentColumnWidth);
        }
        $this->consoleTable->render($headers, $rows, self::MIN_WIDTH);
    }
    /**
     * @return string[]
     */
    private function createRow(TableRow $tableRow, TableView $tableView, int $countColumnWidth, int $percentColumnWidth): array
    {
        $name = $tableRow->isChild() ? '  ' . $tableRow->getName() : $tableRow->getName();
        $row = [$name, str_pad($tableRow->getCount(), $countColumnWidth, ' ', \STR_PAD_LEFT)];
        if ($tableView->isShouldIncludeRelative()) {
            $row[] = str_pad((string) $tableRow->getPercent(), $percentColumnWidth, ' ', \STR_PAD_LEFT);
        }
        return $row;
    }
    private function resolveCountColumnWidth(TableView $tableView): int
    {
        $width = strlen($tableView->getLabel());
        foreach ($tableView->getRows() as $tableRow) {
            $width = max($width, strlen($tableRow->getCount()));
        }
        return $width;
    }
    private function resolvePercentColumnWidth(TableView $tableView): int
    {
        $width = strlen('Relative');
        foreach ($tableView->getRows() as $tableRow) {
            if ($tableRow->getPercent() !== null) {
                $width = max($width, strlen($tableRow->getPercent()));
            }
        }
        return $width;
    }
}
