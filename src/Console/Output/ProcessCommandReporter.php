<?php declare(strict_types=1);

namespace Rector\Console\Output;

use Rector\Console\ConsoleStyle;
use Rector\Contract\Rector\RectorInterface;
use Rector\NodeTraverser\RectorNodeTraverser;
use Rector\Reporting\FileDiff;
use Rector\YamlRector\Contract\YamlRectorInterface;
use Rector\YamlRector\YamlFileProcessor;

final class ProcessCommandReporter
{
    /**
     * @var ConsoleStyle
     */
    private $consoleStyle;

    /**
     * @var RectorNodeTraverser
     */
    private $rectorNodeTraverser;

    /**
     * @var YamlFileProcessor
     */
    private $yamlFileProcessor;

    public function __construct(
        RectorNodeTraverser $rectorNodeTraverser,
        ConsoleStyle $consoleStyle,
        YamlFileProcessor $yamlFileProcessor
    ) {
        $this->consoleStyle = $consoleStyle;
        $this->rectorNodeTraverser = $rectorNodeTraverser;
        $this->yamlFileProcessor = $yamlFileProcessor;
    }

    public function reportLoadedRectors(): void
    {
        $rectorCount = $this->rectorNodeTraverser->getRectorCount() + $this->yamlFileProcessor->getYamlRectorsCount();

        $this->consoleStyle->title(sprintf('%d Loaded Rector%s', $rectorCount, $rectorCount === 1 ? '' : 's'));

        $rectorClasses = array_map(function (RectorInterface $rector): string {
            return get_class($rector);
        }, $this->rectorNodeTraverser->getRectors());

        $yamlRectorClasses = array_map(function (YamlRectorInterface $yamlRector): string {
            return get_class($yamlRector);
        }, $this->yamlFileProcessor->getYamlRectors());

        $this->consoleStyle->listing($rectorClasses + $yamlRectorClasses);
    }

    /**
     * @param string[] $changedFiles
     */
    public function reportChangedFiles(array $changedFiles): void
    {
        if (count($changedFiles) <= 0) {
            return;
        }

        $this->consoleStyle->title(sprintf(
            '%d Changed file%s',
            count($changedFiles),
            count($changedFiles) === 1 ? '' : 's'
        ));
        $this->consoleStyle->listing($changedFiles);
    }

    /**
     * @param FileDiff[] $fileDiffs
     */
    public function reportFileDiffs(array $fileDiffs): void
    {
        if (count($fileDiffs) <= 0) {
            return;
        }

        $this->consoleStyle->title(sprintf(
            '%d file%s with changes',
            count($fileDiffs),
            count($fileDiffs) === 1 ? '' : 's'
        ));

        $i = 0;
        foreach ($fileDiffs as $fileDiff) {
            $this->consoleStyle->writeln(sprintf('<options=bold>%d) %s</>', ++$i, $fileDiff->getFile()));
            $this->consoleStyle->newLine();
            $this->consoleStyle->writeln($fileDiff->getDiff());
            $this->consoleStyle->newLine();
        }
    }
}
