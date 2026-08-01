<?php

declare (strict_types=1);
namespace RectorPrefix202608\Entropy\Console\Input;

use RectorPrefix202608\Entropy\Attributes\RelatedTest;
use RectorPrefix202608\Entropy\Console\ValueObject\CLIRequest;
use RectorPrefix202608\Entropy\Tests\Console\Input\InputParserTest;
final class InputParser
{
    /**
     * @param mixed[] $argv
     */
    public function parse(array $argv): CLIRequest
    {
        // remove script name
        array_shift($argv);
        if ($argv === []) {
            // fallback to show all commands
            return new CLIRequest(null);
        }
        $args = [];
        $options = [];
        $command = array_shift($argv);
        if (strncmp((string) $command, '-', strlen('-')) === 0) {
            // most likely an option
            $options[ltrim((string) $command, '-')] = \true;
            $command = null;
        }
        while ($argv !== []) {
            $item = array_shift($argv);
            // --option or --option=value
            if (strncmp((string) $item, '--', strlen('--')) === 0) {
                [$name, $value] = $this->parseLongOption($item, $argv);
                if (!is_numeric($value)) {
                    // allow multiple param
                    if (!isset($options[$name]) || !is_array($options[$name])) {
                        $options[$name] = [];
                    }
                    $options[$name][] = $value;
                    continue;
                }
                $options[$name] = $value;
                continue;
            }
            // -v
            if (strncmp((string) $item, '-', strlen('-')) === 0) {
                $options[ltrim((string) $item, '-')] = \true;
                continue;
            }
            // positional argument
            $args[] = $item;
        }
        return new CLIRequest($command, $args, $options);
    }
    /**
     * @param array<int, mixed> $argv
     * @return array{mixed, mixed}
     */
    private function parseLongOption(string $item, array &$argv): array
    {
        $item = ltrim($item, '--');
        if (strpos($item, '=') !== \false) {
            return explode('=', $item, 2);
        }
        // --option value
        if ($argv !== [] && strncmp((string) $argv[0], '-', strlen('-')) !== 0) {
            return [$item, array_shift($argv)];
        }
        return [$item, \true];
    }
}
