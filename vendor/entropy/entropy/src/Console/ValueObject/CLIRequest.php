<?php

declare (strict_types=1);
namespace RectorPrefix202609\Entropy\Console\ValueObject;

use RectorPrefix202609\Webmozart\Assert\Assert;
final class CLIRequest
{
    /**
     * @readonly
     */
    private ?string $commandName;
    /**
     * @var mixed[]
     * @readonly
     */
    private array $arguments = [];
    /**
     * @var array<string, mixed>
     */
    private array $options = [];
    /**
     * @param mixed[] $arguments
     * @param array<string, mixed> $options
     */
    public function __construct(?string $commandName, array $arguments = [], array $options = [])
    {
        $this->commandName = $commandName;
        $this->arguments = $arguments;
        $this->options = $options;
        Assert::allString(array_keys($options));
    }
    public function getCommandName(): ?string
    {
        return $this->commandName;
    }
    /**
     * @return mixed[]
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }
    /**
     * Re-interpret a leading token (mistaken for a command name) as the first
     * positional argument of the resolved command.
     */
    public function withCommandNameAndPrependedArgument(string $commandName, string $argument): self
    {
        return new self($commandName, array_merge([$argument], $this->arguments), $this->options);
    }
    /**
     * @return array<string, mixed>
     */
    public function getOptions(): array
    {
        return $this->options;
    }
    /**
     * @param mixed $default
     * @return mixed
     */
    public function option(string $name, $default = null)
    {
        return $this->options[$name] ?? $default;
    }
    public function isCommandHelp(): bool
    {
        if ($this->commandName === null) {
            return \false;
        }
        return array_intersect(['h', 'help'], array_keys($this->options)) !== [];
    }
}
