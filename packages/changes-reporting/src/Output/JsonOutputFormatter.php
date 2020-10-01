<?php

declare(strict_types=1);

namespace Rector\ChangesReporting\Output;

use Nette\Utils\Json;
use Rector\ChangesReporting\Application\ErrorAndDiffCollector;
use Rector\ChangesReporting\Contract\Output\OutputFormatterInterface;
use Rector\Core\Configuration\Configuration;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symplify\SmartFileSystem\SmartFileSystem;

final class JsonOutputFormatter implements OutputFormatterInterface
{
    /**
     * @var string
     */
    public const NAME = 'json';

    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var SmartFileSystem
     */
    private $smartFileSystem;

    public function __construct(
        Configuration $configuration,
        SmartFileSystem $smartFileSystem,
        SymfonyStyle $symfonyStyle
    ) {
        $this->symfonyStyle = $symfonyStyle;
        $this->configuration = $configuration;
        $this->smartFileSystem = $smartFileSystem;
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function report(ErrorAndDiffCollector $errorAndDiffCollector): void
    {
        $errorsArray = [
            'meta' => [
                'version' => $this->configuration->getPrettyVersion(),
                'config' => $this->configuration->getConfigFilePath(),
            ],
            'totals' => [
                'changed_files' => $errorAndDiffCollector->getFileDiffsCount(),
                'removed_and_added_files_count' => $errorAndDiffCollector->getRemovedAndAddedFilesCount(),
                'removed_node_count' => $errorAndDiffCollector->getRemovedNodeCount(),
            ],
        ];

        $fileDiffs = $errorAndDiffCollector->getFileDiffs();
        ksort($fileDiffs);
        foreach ($fileDiffs as $fileDiff) {
            $relativeFilePath = $fileDiff->getRelativeFilePath();

            $errorsArray['file_diffs'][] = [
                'file' => $relativeFilePath,
                'diff' => $fileDiff->getDiff(),
                'applied_rectors' => $fileDiff->getRectorClasses(),
            ];

            // for Rector CI
            $errorsArray['changed_files'][] = $relativeFilePath;
        }

        $errors = $errorAndDiffCollector->getErrors();
        $errorsArray['totals']['errors'] = count($errors);

        foreach ($errors as $error) {
            $errorData = [
                'message' => $error->getMessage(),
                'file' => $error->getFileInfo()
                    ->getPathname(),
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

        $outputFile = $this->configuration->getOutputFile();
        if ($outputFile !== null) {
            $this->smartFileSystem->dumpFile($outputFile, $json . PHP_EOL);
        } else {
            $this->symfonyStyle->writeln($json);
        }
    }
}
