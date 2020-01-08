<?php

declare(strict_types=1);

namespace Rector\Console\Output;

use Nette\Utils\Json;
use Rector\Application\ErrorAndDiffCollector;
use Rector\Configuration\Configuration;
use Rector\Contract\Console\Output\OutputFormatterInterface;
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

    public function report(ErrorAndDiffCollector $errorAndDiffCollector, Configuration $configuration): void
    {
        $fileDiffs = $errorAndDiffCollector->getFileDiffs();

        $errorsArray = [
            'meta' => [
                'version' => $this->configuration->getPrettyVersion(),
                'config' => $this->configuration->getConfigFilePath(),
            ],
            'totals' => [
                'changed_files' => count($fileDiffs),
                'removed_and_added_files_count' => $errorAndDiffCollector->getRemovedAndAddedFilesCount(),
                'removed_node_count' => $errorAndDiffCollector->getRemovedNodeCount(),
            ],
        ];

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

        $this->symfonyStyle->writeln($json);
    }
}
