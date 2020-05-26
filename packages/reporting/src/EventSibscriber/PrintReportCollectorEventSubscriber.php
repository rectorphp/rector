<?php

declare(strict_types=1);

namespace Rector\Reporting\EventSibscriber;

use Rector\ChangesReporting\Output\ConsoleOutputFormatter;
use Rector\Core\Configuration\Configuration;
use Rector\Core\EventDispatcher\Event\AfterReportEvent;
use Rector\Reporting\DataCollector\ReportCollector;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class PrintReportCollectorEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var ReportCollector
     */
    private $reportCollector;

    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    public function __construct(
        ReportCollector $reportCollector,
        Configuration $configuration,
        SymfonyStyle $symfonyStyle
    ) {
        $this->reportCollector = $reportCollector;
        $this->configuration = $configuration;
        $this->symfonyStyle = $symfonyStyle;
    }

    /**
     * @return string[]
     */
    public static function getSubscribedEvents(): array
    {
        return [AfterReportEvent::class => 'printReportCollector'];
    }

    public function printReportCollector(): void
    {
        if ($this->shouldSkip()) {
            return;
        }

        $this->symfonyStyle->title('Collected reports');

        foreach ($this->reportCollector->getReports() as $report) {
            $this->symfonyStyle->writeln($report->getRelativeFilePath() . ':' . $report->getLine());
            $this->symfonyStyle->writeln('* ' . $report->getReport());
            $this->symfonyStyle->writeln('* ' . $report->getRectorClass());

            $this->symfonyStyle->newLine(2);
        }
    }

    private function shouldSkip(): bool
    {
        // print only to console, not json etc.
        if ($this->configuration->getOutputFormat() !== ConsoleOutputFormatter::NAME) {
            return true;
        }

        return $this->reportCollector->getReports() === [];
    }
}
