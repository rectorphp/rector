<?php

declare (strict_types=1);
namespace RectorPrefix202609\Entropy\Console\Input;

use RectorPrefix202609\Entropy\Attribute\RelatedTest;
use RectorPrefix202609\Entropy\Console\CommandRegistry;
use RectorPrefix202609\Entropy\Console\Contract\CommandInterface;
use RectorPrefix202609\Entropy\Console\ValueObject\CLIRequest;
use RectorPrefix202609\Entropy\Reflection\ValueOptionNameResolver;
use RectorPrefix202609\Entropy\Tests\Console\Input\InputParserTest;
use ReflectionMethod;
/**
 * @see \Entropy\Tests\Console\Input\InputParserTest
 */
final class InputParser
{
    /**
     * @readonly
     */
    private CommandRegistry $commandRegistry;
    public function __construct(CommandRegistry $commandRegistry)
    {
        $this->commandRegistry = $commandRegistry;
    }
    /**
     * @param array<int, mixed> $argv
     */
    public function parse(array $argv): CLIRequest
    {
        // remove script name
        array_shift($argv);
        if ($argv === []) {
            // fallback to show all commands
            return new CLIRequest(null);
        }
        // the first non-option token is the command name
        $command = null;
        if (strncmp((string) $argv[0], '-', strlen('-')) !== 0) {
            $command = array_shift($argv);
        }
        // which "--name" options take a value, so a flag never swallows the next token
        $valueOptionNames = $this->resolveValueOptionNames($command);
        $args = [];
        $options = [];
        $optionsEnded = \false;
        while ($argv !== []) {
            $item = array_shift($argv);
            // "--" ends option parsing; everything after it is a positional argument
            if (!$optionsEnded && $item === '--') {
                $optionsEnded = \true;
                continue;
            }
            // --option or --option=value
            if (!$optionsEnded && strncmp((string) $item, '--', strlen('--')) === 0) {
                $name = ltrim((string) $item, '-');
                if (strpos($name, '=') !== \false) {
                    [$name, $value] = explode('=', $name, 2);
                } elseif (isset($valueOptionNames[$name]) && $argv !== [] && $argv[0] !== '--' && strncmp((string) $argv[0], '-', strlen('-')) !== 0) {
                    // only value options consume the next token; a flag never does
                    $value = array_shift($argv);
                } else {
                    $value = \true;
                }
                // flag, no value
                if ($value === \true) {
                    $options[$name] = \true;
                    continue;
                }
                if (is_numeric($value)) {
                    $options[$name] = $value;
                    continue;
                }
                // allow a repeatable value option
                if (!isset($options[$name]) || !is_array($options[$name])) {
                    $options[$name] = [];
                }
                $options[$name][] = $value;
                continue;
            }
            // -v
            if (!$optionsEnded && strncmp((string) $item, '-', strlen('-')) === 0) {
                $options[ltrim((string) $item, '-')] = \true;
                continue;
            }
            // positional argument
            $args[] = $item;
        }
        return new CLIRequest($command, $args, $options);
    }
    /**
     * @return array<string, true>
     */
    private function resolveValueOptionNames(?string $commandName): array
    {
        $command = $this->resolveCommand($commandName);
        if (!$command instanceof CommandInterface) {
            return [];
        }
        return ValueOptionNameResolver::resolve(new ReflectionMethod($command, 'run'));
    }
    private function resolveCommand(?string $commandName): ?CommandInterface
    {
        if ($commandName !== null && $this->commandRegistry->has($commandName)) {
            return $this->commandRegistry->get($commandName);
        }
        // no command name (options first) or an unknown token: fall back to the default command's schema
        return $this->commandRegistry->getDefault();
    }
}
