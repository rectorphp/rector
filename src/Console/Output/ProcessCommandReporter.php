<?php declare(strict_types=1);

namespace Rector\Console\Output;

use Rector\Application\Error;
use Rector\Reporting\FileDiff;
use Symfony\Component\Console\Style\SymfonyStyle;
use function Safe\sprintf;

final class ProcessCommandReporter
{
    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    public function __construct(SymfonyStyle $symfonyStyle)
    {
        $this->symfonyStyle = $symfonyStyle;
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

            if ($fileDiff->getAppliedRectorClasses()) {
                $this->symfonyStyle->writeln('Applied rectors:');
                $this->symfonyStyle->newLine();
                $this->symfonyStyle->listing($fileDiff->getAppliedRectorClasses());
                $this->symfonyStyle->newLine();
            }
        }
    }

    /**
     * @param Error[] $errors
     */
    public function reportErrors(array $errors): void
    {
        foreach ($errors as $error) {
            $message = sprintf(
                'Could not process "%s" file%s, due to: %s"%s".',
                $error->getFileInfo()->getPathname(),
                $error->getRectorClass() ? ' by "' . $error->getRectorClass() . '"' : '',
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
