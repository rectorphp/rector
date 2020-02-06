<?php

declare(strict_types=1);

namespace Rector\Core\Console\Output;

use Nette\Utils\FileSystem;
use Nette\Utils\Json;
use Rector\Core\Application\ErrorAndDiffCollector;
use Rector\Core\Configuration\Configuration;
use Rector\Core\Contract\Console\Output\OutputFormatterInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

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

    public function __construct(SymfonyStyle $symfonyStyle, Configuration $configuration)
    {
        $this->symfonyStyle = $symfonyStyle;
        $this->configuration = $configuration;
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
                'applied_rectors' => $fileDiff->getAppliedRectorClasses(),
            ];

            // for Rector CI
            $errorsArray['changed_files'][] = $relativeFilePath;
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

        $outputFile = $this->configuration->getOutputFile();
        if ($outputFile !== null) {
            FileSystem::write($outputFile, $json . PHP_EOL);
        } else {
            $this->symfonyStyle->writeln($json);
        }
    }
}
