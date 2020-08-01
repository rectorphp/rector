<?php

declare(strict_types=1);

namespace Rector\DocumentationGenerator\OutputFormatter;

use Nette\Utils\Strings;
use Rector\Core\Contract\Rector\RectorInterface;
use Rector\DocumentationGenerator\RectorMetadataResolver;
use Symfony\Component\Console\Style\SymfonyStyle;

final class MarkdownDumpRectorsOutputFormatter
{
    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    /**
     * @var RectorMetadataResolver
     */
    private $rectorMetadataResolver;

    /**
     * @var RectorPrinter
     */
    private $rectorPrinter;

    public function __construct(
        RectorMetadataResolver $rectorMetadataResolver,
        SymfonyStyle $symfonyStyle,
        RectorPrinter $rectorPrinter
    ) {
        $this->symfonyStyle = $symfonyStyle;
        $this->rectorMetadataResolver = $rectorMetadataResolver;
        $this->rectorPrinter = $rectorPrinter;
    }

    /**
     * @param RectorInterface[] $packageRectors
     */
    public function format(array $packageRectors, bool $isRectorProject): void
    {
        $totalRectorCount = count($packageRectors);
        $message = sprintf('# All %d Rectors Overview', $totalRectorCount);

        $this->symfonyStyle->writeln($message);
        $this->symfonyStyle->newLine();

        if ($isRectorProject) {
            $this->symfonyStyle->writeln('- [Projects](#projects)');
            $this->printRectorsWithHeadline($packageRectors, 'Projects');
        } else {
            $this->printRectors($packageRectors, $isRectorProject);
        }
    }

    /**
     * @param RectorInterface[] $rectors
     * @return RectorInterface[][]
     */
    private function groupRectorsByPackage(array $rectors): array
    {
        $rectorsByPackage = [];
        foreach ($rectors as $rector) {
            $rectorClass = get_class($rector);
            $package = $this->rectorMetadataResolver->resolvePackageFromRectorClass($rectorClass);
            $rectorsByPackage[$package][] = $rector;
        }

        // sort groups by name to make them more readable
        ksort($rectorsByPackage);

        return $rectorsByPackage;
    }

    /**
     * @param RectorInterface[][] $rectorsByGroup
     */
    private function printGroupsMenu(array $rectorsByGroup): void
    {
        foreach ($rectorsByGroup as $group => $rectors) {
            $escapedGroup = str_replace('\\', '', $group);
            $escapedGroup = Strings::webalize($escapedGroup, '_');
            $message = sprintf('- [%s](#%s) (%d)', $group, $escapedGroup, count($rectors));

            $this->symfonyStyle->writeln($message);
        }

        $this->symfonyStyle->newLine();
    }

    /**
     * @param RectorInterface[] $rectors
     */
    private function printRectorsWithHeadline(array $rectors, string $headline): void
    {
        if (count($rectors) === 0) {
            return;
        }

        $this->symfonyStyle->writeln('---');
        $this->symfonyStyle->newLine();

        $this->symfonyStyle->writeln('## ' . $headline);
        $this->symfonyStyle->newLine();

        $this->printRectors($rectors, true);
    }

    /**
     * @param RectorInterface[] $rectors
     */
    private function printRectors(array $rectors, bool $isRectorProject): void
    {
        $groupedRectors = $this->groupRectorsByPackage($rectors);

        if ($isRectorProject) {
            $this->printGroupsMenu($groupedRectors);
        }

        foreach ($groupedRectors as $group => $rectors) {
            if ($isRectorProject) {
                $this->symfonyStyle->writeln('## ' . $group);
                $this->symfonyStyle->newLine();
            }

            foreach ($rectors as $rector) {
                $this->rectorPrinter->printRector($rector, $isRectorProject);
            }
        }
    }
}
