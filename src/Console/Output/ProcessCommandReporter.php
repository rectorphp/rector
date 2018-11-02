<?php declare(strict_types=1);

namespace Rector\Console\Output;

use Rector\Application\Error;
use Rector\Contract\Rector\RectorInterface;
use Rector\NodeTraverser\RectorNodeTraverser;
use Rector\Reporting\FileDiff;
use Rector\YamlRector\YamlFileProcessor;
use Symfony\Component\Console\Style\SymfonyStyle;
use function Safe\sprintf;

final class ProcessCommandReporter
{
    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

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
        SymfonyStyle $symfonyStyle,
        YamlFileProcessor $yamlFileProcessor
    ) {
        $this->symfonyStyle = $symfonyStyle;
        $this->rectorNodeTraverser = $rectorNodeTraverser;
        $this->yamlFileProcessor = $yamlFileProcessor;
    }

    public function reportLoadedRectors(): void
    {
        $rectorCount = $this->rectorNodeTraverser->getRectorCount() + $this->yamlFileProcessor->getYamlRectorsCount();

        $this->symfonyStyle->title(sprintf('%d Loaded Rector%s', $rectorCount, $rectorCount === 1 ? '' : 's'));

        $allRectors = array_merge(
            $this->rectorNodeTraverser->getRectors() + $this->yamlFileProcessor->getYamlRectors()
        );

        $rectorClasses = array_map(function (RectorInterface $rector): string {
            return get_class($rector);
        }, $allRectors);

        $this->symfonyStyle->listing($rectorClasses);
    }

    /**
     * @param string[] $changedFiles
     */
    public function reportChangedFiles(array $changedFiles): void
    {
        if (count($changedFiles) <= 0) {
            return;
        }

        $this->symfonyStyle->title(
            sprintf('%d Changed file%s', count($changedFiles), count($changedFiles) === 1 ? '' : 's')
        );
        $this->symfonyStyle->listing($changedFiles);
    }

    /**
     * @param FileDiff[] $fileDiffs
     */
    public function reportFileDiffs(array $fileDiffs): void
    {
        if (count($fileDiffs) <= 0) {
            return;
        }

        $this->symfonyStyle->title(
            sprintf('%d file%s with changes', count($fileDiffs), count($fileDiffs) === 1 ? '' : 's')
        );

        $i = 0;
        foreach ($fileDiffs as $fileDiff) {
            $this->symfonyStyle->writeln(sprintf('<options=bold>%d) %s</>', ++$i, $fileDiff->getFile()));
            $this->symfonyStyle->newLine();
            $this->symfonyStyle->writeln($fileDiff->getDiff());
            $this->symfonyStyle->newLine();
        }
    }

    /**
     * @param Error[] $errors
     */
    public function reportErrors(array $errors): void
    {
        foreach ($errors as $error) {
            $message = sprintf(
                'Could not process "%s" file, due to: %s"%s".',
                $error->getFileInfo()->getPathname(),
                PHP_EOL,
                $error->getMessage()
            );

            if ($error->getLine()) {
                $message .= ' On line: ' . $error->getLine();
            }

            $this->symfonyStyle->error($message);
        }
    }
}
