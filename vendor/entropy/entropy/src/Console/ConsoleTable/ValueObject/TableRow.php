<?php

declare (strict_types=1);
namespace RectorPrefix202608\Entropy\Console\ConsoleTable\ValueObject;

final class TableRow
{
    /**
     * @readonly
     */
    private string $name;
    /**
     * @readonly
     */
    private string $count;
    /**
     * @readonly
     */
    private ?string $percent;
    /**
     * @readonly
     */
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
