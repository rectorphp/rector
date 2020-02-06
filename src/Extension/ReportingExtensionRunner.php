<?php

declare(strict_types=1);

namespace Rector\Core\Extension;

use Rector\Core\Contract\Extension\ReportingExtensionInterface;

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
