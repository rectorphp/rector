<?php

declare (strict_types=1);
namespace RectorPrefix202610\Entropy\Console\ValueObject;

final class Option
{
    private string $name;
    private string $type;
    private ?string $description;
    private bool $acceptsMultipleValues;
    /**
     * @var string|bool|int|null
     */
    private $defaultValue;
    /**
     * @param string|bool|int|null $defaultValue
     */
    public function __construct(string $name, string $type, ?string $description = null, bool $acceptsMultipleValues = \false, $defaultValue = null)
    {
        $this->type = $type;
        $this->description = $description;
        $this->acceptsMultipleValues = $acceptsMultipleValues;
        $this->defaultValue = $defaultValue;
        // rename parameter name to -- option name, camelCase to kebab-case conversion
        $this->name = strtolower((string) preg_replace('/([a-z])([A-Z])/', '$1-$2', $name));
    }
    public function getName(): string
    {
        return $this->name;
    }
    public function getDescription(): ?string
    {
        return $this->description;
    }
    /**
     * @return int|string|bool|null
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }
    public function doesAcceptMultipleValues(): bool
    {
        return $this->acceptsMultipleValues;
    }
    public function getType(): string
    {
        return $this->type;
    }
}
