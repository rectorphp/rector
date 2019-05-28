<?php declare(strict_types=1);

namespace Rector\Console\Output;

use Nette\Utils\Json;
use Rector\Application\ErrorAndDiffCollector;
use Rector\Contract\Console\Output\OutputFormatterInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class JsonOutputFormatter implements OutputFormatterInterface
{
    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    public function __construct(SymfonyStyle $symfonyStyle)
    {
        $this->symfonyStyle = $symfonyStyle;
    }

    public function getName(): string
    {
        return 'json';
    }

    public function report(ErrorAndDiffCollector $errorAndDiffCollector): void
    {
        $fileDiffs = $errorAndDiffCollector->getFileDiffs();

        $errorsArray = [];
        $errorsArray['totals']['changed_files'] = count($fileDiffs);

        ksort($fileDiffs);

        foreach ($fileDiffs as $fileDiff) {
            $errorsArray['file_diffs'][] = [
                'file' => $fileDiff->getFile(),
                'diff' => $fileDiff->getDiff(),
                'applied_rectors' => $fileDiff->getAppliedRectorClasses(),
            ];
        }

        $errors = $errorAndDiffCollector->getErrors();
        $errorsArray['totals']['errors'] = count($errors);

        foreach ($errors as $error) {
            $errorData = [
                'message' => $error->getMessage(),
                'file' => $error->getFileInfo()->getPathname(),
            ];

            if ($error->getRectorClass()) {
                $errorData['caused_by'] = $error->getRectorClass();
            }

            if ($error->getLine() !== null) {
                $errorData['line'] = $error->getLine();
            }

            $errorsArray['errors'][] = $errorData;
        }

        $json = Json::encode($errorsArray, Json::PRETTY);

        $this->symfonyStyle->writeln($json);
    }
}
