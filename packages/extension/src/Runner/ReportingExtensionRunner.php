<?php

declare(strict_types=1);

namespace Rector\Extension\Runner;

use Rector\Extension\Contract\ReportingExtensionInterface;

/**
 * @todo refactor to event system
 */
final class ReportingExtensionRunner
{
    /**
     * @var ReportingExtensionInterface[]
     */
    private $reportingExtensions = [];

    /**
     * @param ReportingExtensionInterface[] $reportingExtensions
     */
    public function __construct(array $reportingExtensions = [])
    {
        $this->reportingExtensions = $reportingExtensions;
    }

    public function run(): void
    {
        foreach ($this->reportingExtensions as $reportingExtension) {
            $reportingExtension->run();
        }
    }
}
