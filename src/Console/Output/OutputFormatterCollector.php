<?php

declare (strict_types=1);
namespace Rector\Core\Console\Output;

use Rector\ChangesReporting\Contract\Output\OutputFormatterInterface;
use Rector\Core\Exception\Configuration\InvalidConfigurationException;
final class OutputFormatterCollector
{
    /**
     * @var array<string, OutputFormatterInterface>
     */
    private $outputFormatters = [];
    /**
     * @param OutputFormatterInterface[] $outputFormatters
     */
    public function __construct(array $outputFormatters)
    {
        foreach ($outputFormatters as $outputFormatter) {
            $this->outputFormatters[$outputFormatter->getName()] = $outputFormatter;
        }
    }
    public function getByName(string $name) : OutputFormatterInterface
    {
        $this->ensureOutputFormatExists($name);
        return $this->outputFormatters[$name];
    }
    private function ensureOutputFormatExists(string $name) : void
    {
        if (isset($this->outputFormatters[$name])) {
            return;
        }
        $outputFormatterNames = \array_keys($this->outputFormatters);
        throw new InvalidConfigurationException(\sprintf('Output formatter "%s" was not found. Pick one of "%s".', $name, \implode('", "', $outputFormatterNames)));
    }
}
