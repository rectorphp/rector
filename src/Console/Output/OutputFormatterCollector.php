<?php

declare(strict_types=1);

namespace Rector\Console\Output;

use Rector\Contract\Console\Output\OutputFormatterInterface;
use Rector\Exception\Console\Output\MissingOutputFormatterException;

final class OutputFormatterCollector
{
    /**
     * @var OutputFormatterInterface[]
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

    public function getByName(string $name): OutputFormatterInterface
    {
        $this->ensureOutputFormatExists($name);

        return $this->outputFormatters[$name];
    }

    /**
     * @return string[]
     */
    public function getNames(): array
    {
        return array_keys($this->outputFormatters);
    }

    private function ensureOutputFormatExists(string $name): void
    {
        if (isset($this->outputFormatters[$name])) {
            return;
        }

        throw new MissingOutputFormatterException(sprintf(
            'Output formatter "%s" was not found. Pick one of "%s".',
            $name,
            implode('", "', $this->getNames())
        ));
    }
}
