<?php

declare (strict_types=1);
namespace RectorPrefix202608\Entropy\Console\ConsoleTable\ValueObject;

use RectorPrefix202608\Webmozart\Assert\Assert;
final class TableView
{
    /**
     * @readonly
     */
    private string $title;
    /**
     * @readonly
     */
    private string $label;
    /**
     * @var TableRow[]
     * @readonly
     */
    private array $tableRows;
    /**
     * @readonly
     */
    private bool $shouldIncludeRelative = \false;
    /**
     * @param TableRow[] $tableRows
     */
    public function __construct(string $title, string $label, array $tableRows, bool $shouldIncludeRelative = \false)
    {
        $this->title = $title;
        $this->label = $label;
        $this->tableRows = $tableRows;
        $this->shouldIncludeRelative = $shouldIncludeRelative;
        Assert::allIsInstanceOf($tableRows, TableRow::class);
    }
    public function getTitle(): string
    {
        return $this->title;
    }
    public function getLabel(): string
    {
        return $this->label;
    }
    public function isShouldIncludeRelative(): bool
    {
        return $this->shouldIncludeRelative;
    }
    /**
     * @return TableRow[]
     */
    public function getRows(): array
    {
        return $this->tableRows;
    }
}
