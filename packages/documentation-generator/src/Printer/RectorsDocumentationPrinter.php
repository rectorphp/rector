<?php

declare(strict_types=1);

namespace Rector\DocumentationGenerator\Printer;

use Nette\Utils\Strings;
use Rector\Core\Contract\Rector\RectorInterface;
use Rector\DocumentationGenerator\RectorMetadataResolver;
use Webmozart\Assert\Assert;

/**
 * @see \Rector\DocumentationGenerator\Tests\Printer\RectorsDocumentationPrinter\RectorsDocumentationPrinterTest
 */
final class RectorsDocumentationPrinter
{
    /**
     * @var RectorMetadataResolver
     */
    private $rectorMetadataResolver;

    /**
     * @var RectorPrinter
     */
    private $rectorPrinter;

    public function __construct(RectorMetadataResolver $rectorMetadataResolver, RectorPrinter $rectorPrinter)
    {
        $this->rectorMetadataResolver = $rectorMetadataResolver;
        $this->rectorPrinter = $rectorPrinter;
    }

    /**
     * @param RectorInterface[] $rectors
     */
    public function print(array $rectors, bool $isRectorProject): string
    {
        Assert::allIsInstanceOf($rectors, RectorInterface::class);

        $totalRectorCount = count($rectors);
        $message = sprintf('# All %d Rectors Overview', $totalRectorCount) . PHP_EOL;

        $content = $message;
        $content .= PHP_EOL;

        if ($isRectorProject) {
            $content .= '- [Projects](#projects)';
            $content .= PHP_EOL;

            $content .= $this->printRectorsWithHeadline($rectors, 'Projects');
        } else {
            $content .= $this->printRectors($rectors, $isRectorProject);
        }

        return $content;
    }

    /**
     * @param RectorInterface[] $rectors
     */
    public function printRectors(array $rectors, bool $isRectorProject): string
    {
        $groupedRectors = $this->groupRectorsByPackage($rectors);

        $content = '';
        if ($isRectorProject) {
            $content .= $this->printGroupsMenu($groupedRectors);
        }

        foreach ($groupedRectors as $group => $rectors) {
            if ($isRectorProject) {
                $content .= '## ' . $group . PHP_EOL . PHP_EOL;
            }

            foreach ($rectors as $rector) {
                $rectorContent = $this->rectorPrinter->printRector($rector, $isRectorProject);
                $content .= $rectorContent . PHP_EOL;
            }
        }

        return $content;
    }

    /**
     * @param RectorInterface[] $rectors
     */
    private function printRectorsWithHeadline(array $rectors, string $headline): string
    {
        if ($rectors === []) {
            return '';
        }

        $content = '---' . PHP_EOL . PHP_EOL;
        $content .= '## ' . $headline . PHP_EOL . PHP_EOL;

        $content .= $this->printRectors($rectors, true);

        return $content;
    }

    /**
     * @param RectorInterface[] $rectors
     * @return array<string, RectorInterface[]>
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
    private function printGroupsMenu(array $rectorsByGroup): string
    {
        $content = '';
        foreach ($rectorsByGroup as $group => $rectors) {
            $escapedGroup = str_replace('\\', '', $group);
            $escapedGroup = Strings::webalize($escapedGroup, '_');
            $message = sprintf('- [%s](#%s) (%d)', $group, $escapedGroup, count($rectors));
            $content .= $message . PHP_EOL;
        }

        return $content . PHP_EOL;
    }
}
