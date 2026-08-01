<?php

declare (strict_types=1);
namespace RectorPrefix202608\Entropy\Console\Output;

use RectorPrefix202608\Entropy\Attributes\RelatedTest;
use RectorPrefix202608\Entropy\Console\Contract\CommandInterface;
use RectorPrefix202608\Entropy\Console\Mapper\CommandRunParametersMapper;
use RectorPrefix202608\Entropy\Console\Terminal\Terminal;
use RectorPrefix202608\Entropy\Console\ValueObject\Argument;
use RectorPrefix202608\Entropy\Console\ValueObject\Option;
use RectorPrefix202608\Entropy\Tests\Console\Output\CommandHelpFactory\CommandHelpFactoryTest;
final class CommandHelpFactory
{
    /**
     * @readonly
     */
    private CommandRunParametersMapper $commandRunParametersMapper;
    public function __construct(CommandRunParametersMapper $commandRunParametersMapper)
    {
        $this->commandRunParametersMapper = $commandRunParametersMapper;
    }
    public function build(CommandInterface $command): string
    {
        $help = [];
        $help[] = '  ' . $command->getDescription();
        $help[] = '';
        $argumentsAndOptions = $this->commandRunParametersMapper->map($command);
        // Arguments
        if ($argumentsAndOptions->getArguments() !== []) {
            $help[] = '<fg=yellow>Arguments:</>';
            foreach ($argumentsAndOptions->getArguments() as $argument) {
                $help[] = $this->formatParameterLine($argument);
            }
            $help[] = '';
        }
        // Options
        if ($argumentsAndOptions->getOptions() !== []) {
            $help[] = '<fg=yellow>Options:</>';
            foreach ($argumentsAndOptions->getOptions() as $option) {
                $help[] = $this->formatParameterLine($option);
            }
            $help[] = '';
        }
        return implode(\PHP_EOL, $help);
    }
    /**
     * @param \Entropy\Console\ValueObject\Argument|\Entropy\Console\ValueObject\Option $argumentOrOption
     */
    private function formatParameterLine($argumentOrOption): string
    {
        $description = trim((string) $argumentOrOption->getDescription());
        $nameWithDefaultValue = $this->nameWithDefaultValue($argumentOrOption);
        $parameterLine = sprintf('  <fg=green>%s</>  %s', Terminal::padVisibleRight($nameWithDefaultValue, 17), $description);
        return rtrim($parameterLine);
    }
    /**
     * @param \Entropy\Console\ValueObject\Option|\Entropy\Console\ValueObject\Argument $argumentOrOption
     */
    private function nameWithDefaultValue($argumentOrOption): string
    {
        if ($argumentOrOption instanceof Option) {
            $contents = '--' . $argumentOrOption->getName();
            $defaultValue = $argumentOrOption->getDefaultValue();
            if ($defaultValue !== null && $defaultValue !== \false) {
                if ($defaultValue === \true) {
                    // avoid casting boolean true to "1"
                    $defaultValue = 'true';
                }
                $contents .= sprintf('</><fg=yellow>=[%s]', $defaultValue);
            } elseif ($argumentOrOption->getType() === 'array') {
                $contents .= '</><fg=yellow>=""';
            }
        } else {
            $contents = $argumentOrOption->getName();
        }
        if ($argumentOrOption->doesAcceptMultipleValues()) {
            $contents .= '</> <fg=yellow>(many)';
        }
        return $contents;
    }
}
