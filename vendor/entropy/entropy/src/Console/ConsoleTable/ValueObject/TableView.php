<?php

declare (strict_types=1);
namespace RectorPrefix202609\Entropy\Console\ConsoleTable\ValueObject;

use RectorPrefix202609\Entropy\Validation\Assert;
final class TableView
{
    private string $title;
    private string $label;
    /**
     * @var TableRow[]
     */
    private array $tableRows;
    private bool $shouldIncludeRelative;
    /**
     * @param TableRow[] $tableRows
     */
    public function __construct(string $title, string $label, array $tableRows, bool $shouldIncludeRelative = \false)
    {
        Assert::allIsInstanceOf($tableRows, TableRow::class);
        $this->title = $title;
        $this->label = $label;
        $this->tableRows = $tableRows;
        $this->shouldIncludeRelative = $shouldIncludeRelative;
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
