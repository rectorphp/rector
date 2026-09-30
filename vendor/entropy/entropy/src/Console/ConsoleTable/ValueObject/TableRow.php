<?php

declare (strict_types=1);
namespace RectorPrefix202609\Entropy\Console\ConsoleTable\ValueObject;

final class TableRow
{
    private string $name;
    private string $count;
    private ?string $percent;
    private bool $isChild;
    public function __construct(string $name, string $count, ?string $percent, bool $isChild)
    {
        $this->name = $name;
        $this->count = $count;
        $this->percent = $percent;
        $this->isChild = $isChild;
    }
    public function getName(): string
    {
        return $this->name;
    }
    public function getCount(): string
    {
        return $this->count;
    }
    public function getPercent(): ?string
    {
        return $this->percent;
    }
    public function isChild(): bool
    {
        return $this->isChild;
    }
}
