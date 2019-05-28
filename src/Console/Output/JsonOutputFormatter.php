<?php declare(strict_types=1);

namespace Rector\Console\Output;

use Nette\Utils\Json;
use Rector\Application\Error;
use Rector\Contract\Console\Output\OutputFormatterInterface;
use Rector\Reporting\FileDiff;
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

    /**
     * @param FileDiff[] $fileDiffs
     */
    public function reportFileDiffs(array $fileDiffs): void
    {
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

        $json = Json::encode($errorsArray, Json::PRETTY);

        $this->symfonyStyle->writeln($json);
    }

    /**
     * @param Error[] $errors
     */
    public function reportErrors(array $errors): void
    {
        $errorsArray = [];
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
