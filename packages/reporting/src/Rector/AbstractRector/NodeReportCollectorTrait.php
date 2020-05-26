<?php

declare(strict_types=1);

namespace Rector\Reporting\Rector\AbstractRector;

use PhpParser\Node;
use Rector\Reporting\DataCollector\ReportCollector;

/**
 * This could be part of @see AbstractRector, but decopuling to trait
 * makes clear what code has 1 purpose.
 */
trait NodeReportCollectorTrait
{
    /**
     * @var ReportCollector
     */
    protected $reportCollector;

    /**
     * @required
     */
    public function autowireNodeReportCollectorTrait(ReportCollector $reportCollector): void
    {
        $this->reportCollector = $reportCollector;
    }

    protected function addReport(string $report, Node $node, string $rectorClass): void
    {
        $this->reportCollector->addFileAndLineAwareReport($report, $node, $rectorClass);
    }
}
