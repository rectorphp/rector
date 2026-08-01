<?php

declare (strict_types=1);
namespace RectorPrefix202608\Entropy\Console;

use RectorPrefix202608\Entropy\Console\Contract\CommandInterface;
use RectorPrefix202608\Entropy\Console\Contract\DefaultCommandInterface;
use RectorPrefix202608\Entropy\Console\Contract\HiddenCommandInterface;
use RectorPrefix202608\Entropy\Console\Exception\InvalidCommandException;
use RectorPrefix202608\Entropy\Utils\FuzzyMatcher;
use RectorPrefix202608\Webmozart\Assert\Assert;
final class CommandRegistry
{
    /**
     * @var CommandInterface[]
     * @readonly
     */
    private array $commands;
    /**
     * @param CommandInterface[] $commands
     */
    public function __construct(array $commands)
    {
        $this->commands = $commands;
        if ($commands === []) {
            throw new InvalidCommandException('Register at least one command, so application can run');
        }
        Assert::allIsInstanceOf($commands, CommandInterface::class);
        $existingNames = [];
        foreach ($commands as $command) {
            // make sure the commandName is registered just once
            if (in_array($command->getName(), $existingNames, \true)) {
                throw new InvalidCommandException(sprintf('Duplicate command commandName: "%s"', $command->getName()));
            }
            $existingNames[] = $command->getName();
            $this->validateCommand($command);
        }
    }
    public function getCommandNameMaxLength(): int
    {
        $maxCommandNameLength = 0;
        foreach ($this->commands as $command) {
            $maxCommandNameLength = max($maxCommandNameLength, strlen($command->getName()));
        }
        return $maxCommandNameLength;
    }
    /**
     * @api used in tests and by applications inspecting registered commands
     *
     * @return CommandInterface[]
     */
    public function all(): array
    {
        return $this->commands;
    }
    /**
     * Commands that should be listed in help output (hidden ones are excluded).
     *
     * @return CommandInterface[]
     */
    public function getVisible(): array
    {
        return array_values(array_filter($this->commands, static fn(CommandInterface $command): bool => !$command instanceof HiddenCommandInterface));
    }
    public function getDefault(): ?CommandInterface
    {
        foreach ($this->commands as $command) {
            if ($command instanceof DefaultCommandInterface) {
                return $command;
            }
        }
        return null;
    }
    public function has(string $commandName): bool
    {
        foreach ($this->commands as $command) {
            if ($commandName === $command->getName()) {
                return \true;
            }
        }
        $matchedCommandName = FuzzyMatcher::match($commandName, $this->getCommandsNames());
        return $matchedCommandName !== null;
    }
    public function get(string $commandName): CommandInterface
    {
        $matchedCommandName = FuzzyMatcher::match($commandName, $this->getCommandsNames());
        foreach ($this->commands as $command) {
            if ($command->getName() === $matchedCommandName) {
                return $command;
            }
        }
        throw new InvalidCommandException(sprintf('Command not found: "%s". Try one of "%s"', $commandName, implode('", "', $this->getCommandsNames())));
    }
    private function validateCommand(CommandInterface $command): void
    {
        $name = $command->getName();
        if ($name === '') {
            throw new InvalidCommandException('Command commandName cannot be empty');
        }
        if ($command->getDescription() === '') {
            throw new InvalidCommandException('Command description cannot be empty');
        }
        if (!method_exists($command, 'run')) {
            throw new InvalidCommandException(sprintf('Command "%s" must have a public "run()" method', $name));
        }
    }
    /**
     * @return string[]
     */
    private function getCommandsNames(): array
    {
        $commandNames = [];
        foreach ($this->commands as $command) {
            $commandNames[] = $command->getName();
        }
        return $commandNames;
    }
}
