<?php

declare (strict_types=1);
namespace Rector\Core\Console\Output;

use Rector\ListReporting\Contract\Output\ShowOutputFormatterInterface;
use RectorPrefix20211020\Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
final class ShowOutputFormatterCollector
{
    /**
     * @var ShowOutputFormatterInterface[]
     */
    private $outputFormatters = [];
    /**
     * @param ShowOutputFormatterInterface[] $showOutputFormatters
     */
    public function __construct(array $showOutputFormatters)
    {
        foreach ($showOutputFormatters as $showOutputFormatter) {
            $this->outputFormatters[$showOutputFormatter->getName()] = $showOutputFormatter;
        }
    }
    public function getByName(string $name) : \Rector\ListReporting\Contract\Output\ShowOutputFormatterInterface
    {
        $this->ensureOutputFormatExists($name);
        return $this->outputFormatters[$name];
    }
    /**
     * @return int[]|string[]
     */
    public function getNames() : array
    {
        return \array_keys($this->outputFormatters);
    }
    private function ensureOutputFormatExists(string $name) : void
    {
        if (isset($this->outputFormatters[$name])) {
            return;
        }
        throw new \RectorPrefix20211020\Symfony\Component\Config\Definition\Exception\InvalidConfigurationException(\sprintf('Output formatter "%s" was not found. Pick one of "%s".', $name, \implode('", "', $this->getNames())));
    }
}
