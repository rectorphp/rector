<?php

declare (strict_types=1);
namespace RectorPrefix202610\Entropy\Console\ValueObject;

final class Argument
{
    private string $name;
    private ?string $description;
    private bool $acceptsMultipleValues;
    public function __construct(string $name, ?string $description = null, bool $acceptsMultipleValues = \false)
    {
        $this->name = $name;
        $this->description = $description;
        $this->acceptsMultipleValues = $acceptsMultipleValues;
    }
    public function getName(): string
    {
        return $this->name;
    }
    public function getDescription(): ?string
    {
        return $this->description;
    }
    public function doesAcceptMultipleValues(): bool
    {
        return $this->acceptsMultipleValues;
    }
}
