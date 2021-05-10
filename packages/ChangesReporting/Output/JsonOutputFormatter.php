<?php

declare(strict_types=1);

namespace Rector\ChangesReporting\Output;

use Nette\Utils\Json;
use Rector\ChangesReporting\Annotation\RectorsChangelogResolver;
use Rector\ChangesReporting\Contract\Output\OutputFormatterInterface;
use Rector\Core\Configuration\Configuration;
use Rector\Core\ValueObject\ProcessResult;
use Symplify\SmartFileSystem\SmartFileSystem;

final class JsonOutputFormatter implements OutputFormatterInterface
{
    /**
     * @var string
     */
    public const NAME = 'json';

    public function __construct(
        private Configuration $configuration,
        private SmartFileSystem $smartFileSystem,
        private RectorsChangelogResolver $rectorsChangelogResolver
    ) {
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function report(ProcessResult $processResult): void
    {
        $errorsArray = [
            'meta' => [
                'version' => $this->configuration->getPrettyVersion(),
                'config' => $this->configuration->getMainConfigFilePath(),
            ],
            'totals' => [
                'changed_files' => count($processResult->getFileDiffs()),
                'removed_and_added_files_count' => $processResult->getRemovedAndAddedFilesCount(),
                'removed_node_count' => $processResult->getRemovedNodeCount(),
            ],
        ];

        $fileDiffs = $processResult->getFileDiffs();
        ksort($fileDiffs);
        foreach ($fileDiffs as $fileDiff) {
            $relativeFilePath = $fileDiff->getRelativeFilePath();

            $appliedRectorsWithChangelog = $this->rectorsChangelogResolver->resolve($fileDiff->getRectorClasses());

            $errorsArray['file_diffs'][] = [
                'file' => $relativeFilePath,
                'diff' => $fileDiff->getDiff(),
                'applied_rectors' => $fileDiff->getRectorClasses(),
                'applied_rectors_with_changelog' => $appliedRectorsWithChangelog,
            ];

            // for Rector CI
            $errorsArray['changed_files'][] = $relativeFilePath;
        }

        $errors = $processResult->getErrors();
        $errorsArray['totals']['errors'] = count($errors);

        $errorsData = $this->createErrorsData($errors);
        if ($errorsData !== []) {
            $errorsArray['errors'] = $errorsData;
        }

        $json = Json::encode($errorsArray, Json::PRETTY);

        $outputFile = $this->configuration->getOutputFile();
        if ($outputFile !== null) {
            $this->smartFileSystem->dumpFile($outputFile, $json . PHP_EOL);
        } else {
            echo $json . PHP_EOL;
        }
    }

    /**
     * @param mixed[] $errors
     * @return mixed[]
     */
    private function createErrorsData(array $errors): array
    {
        $errorsData = [];

        foreach ($errors as $error) {
            $errorData = [
                'message' => $error->getMessage(),
                'file' => $error->getRelativeFilePath(),
            ];

            if ($error->getRectorClass()) {
                $errorData['caused_by'] = $error->getRectorClass();
            }

            if ($error->getLine() !== null) {
                $errorData['line'] = $error->getLine();
            }

            $errorsData[] = $errorData;
        }

        return $errorsData;
    }
}
