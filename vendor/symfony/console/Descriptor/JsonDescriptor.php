<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20211020\Symfony\Component\Console\Descriptor;

use RectorPrefix20211020\Symfony\Component\Console\Application;
use RectorPrefix20211020\Symfony\Component\Console\Command\Command;
use RectorPrefix20211020\Symfony\Component\Console\Input\InputArgument;
use RectorPrefix20211020\Symfony\Component\Console\Input\InputDefinition;
use RectorPrefix20211020\Symfony\Component\Console\Input\InputOption;
/**
 * JSON descriptor.
 *
 * @author Jean-François Simon <contact@jfsimon.fr>
 *
 * @internal
 */
class JsonDescriptor extends \RectorPrefix20211020\Symfony\Component\Console\Descriptor\Descriptor
{
    /**
     * {@inheritdoc}
     * @param \Symfony\Component\Console\Input\InputArgument $argument
     * @param mixed[] $options
     */
    protected function describeInputArgument($argument, $options = [])
    {
        $this->writeData($this->getInputArgumentData($argument), $options);
    }
    /**
     * {@inheritdoc}
     * @param \Symfony\Component\Console\Input\InputOption $option
     * @param mixed[] $options
     */
    protected function describeInputOption($option, $options = [])
    {
        $this->writeData($this->getInputOptionData($option), $options);
        if ($option->isNegatable()) {
            $this->writeData($this->getInputOptionData($option, \true), $options);
        }
    }
    /**
     * {@inheritdoc}
     * @param \Symfony\Component\Console\Input\InputDefinition $definition
     * @param mixed[] $options
     */
    protected function describeInputDefinition($definition, $options = [])
    {
        $this->writeData($this->getInputDefinitionData($definition), $options);
    }
    /**
     * {@inheritdoc}
     * @param \Symfony\Component\Console\Command\Command $command
     * @param mixed[] $options
     */
    protected function describeCommand($command, $options = [])
    {
        $this->writeData($this->getCommandData($command, $options['short'] ?? \false), $options);
    }
    /**
     * {@inheritdoc}
     * @param \Symfony\Component\Console\Application $application
     * @param mixed[] $options
     */
    protected function describeApplication($application, $options = [])
    {
        $describedNamespace = $options['namespace'] ?? null;
        $description = new \RectorPrefix20211020\Symfony\Component\Console\Descriptor\ApplicationDescription($application, $describedNamespace, \true);
        $commands = [];
        foreach ($description->getCommands() as $command) {
            $commands[] = $this->getCommandData($command, $options['short'] ?? \false);
        }
        $data = [];
        if ('UNKNOWN' !== $application->getName()) {
            $data['application']['name'] = $application->getName();
            if ('UNKNOWN' !== $application->getVersion()) {
                $data['application']['version'] = $application->getVersion();
            }
        }
        $data['commands'] = $commands;
        if ($describedNamespace) {
            $data['namespace'] = $describedNamespace;
        } else {
            $data['namespaces'] = \array_values($description->getNamespaces());
        }
        $this->writeData($data, $options);
    }
    /**
     * Writes data as json.
     */
    private function writeData(array $data, array $options)
    {
        $flags = $options['json_encoding'] ?? 0;
        $this->write(\json_encode($data, $flags));
    }
    private function getInputArgumentData(\RectorPrefix20211020\Symfony\Component\Console\Input\InputArgument $argument) : array
    {
        return ['name' => $argument->getName(), 'is_required' => $argument->isRequired(), 'is_array' => $argument->isArray(), 'description' => \preg_replace('/\\s*[\\r\\n]\\s*/', ' ', $argument->getDescription()), 'default' => \INF === $argument->getDefault() ? 'INF' : $argument->getDefault()];
    }
    private function getInputOptionData(\RectorPrefix20211020\Symfony\Component\Console\Input\InputOption $option, bool $negated = \false) : array
    {
        return $negated ? ['name' => '--no-' . $option->getName(), 'shortcut' => '', 'accept_value' => \false, 'is_value_required' => \false, 'is_multiple' => \false, 'description' => 'Negate the "--' . $option->getName() . '" option', 'default' => \false] : ['name' => '--' . $option->getName(), 'shortcut' => $option->getShortcut() ? '-' . \str_replace('|', '|-', $option->getShortcut()) : '', 'accept_value' => $option->acceptValue(), 'is_value_required' => $option->isValueRequired(), 'is_multiple' => $option->isArray(), 'description' => \preg_replace('/\\s*[\\r\\n]\\s*/', ' ', $option->getDescription()), 'default' => \INF === $option->getDefault() ? 'INF' : $option->getDefault()];
    }
    private function getInputDefinitionData(\RectorPrefix20211020\Symfony\Component\Console\Input\InputDefinition $definition) : array
    {
        $inputArguments = [];
        foreach ($definition->getArguments() as $name => $argument) {
            $inputArguments[$name] = $this->getInputArgumentData($argument);
        }
        $inputOptions = [];
        foreach ($definition->getOptions() as $name => $option) {
            $inputOptions[$name] = $this->getInputOptionData($option);
            if ($option->isNegatable()) {
                $inputOptions['no-' . $name] = $this->getInputOptionData($option, \true);
            }
        }
        return ['arguments' => $inputArguments, 'options' => $inputOptions];
    }
    private function getCommandData(\RectorPrefix20211020\Symfony\Component\Console\Command\Command $command, bool $short = \false) : array
    {
        $data = ['name' => $command->getName(), 'description' => $command->getDescription()];
        if ($short) {
            $data += ['usage' => $command->getAliases()];
        } else {
            $command->mergeApplicationDefinition(\false);
            $data += ['usage' => \array_merge([$command->getSynopsis()], $command->getUsages(), $command->getAliases()), 'help' => $command->getProcessedHelp(), 'definition' => $this->getInputDefinitionData($command->getDefinition())];
        }
        $data['hidden'] = $command->isHidden();
        return $data;
    }
}
