<?php

declare (strict_types=1);
namespace Rector\ChangesReporting\Output\Factory;

use RectorPrefix202604\Nette\Utils\Json;
use Rector\Parallel\ValueObject\Bridge;
use Rector\ValueObject\Configuration;
use Rector\ValueObject\Error\SystemError;
use Rector\ValueObject\ProcessResult;
/**
 * @see \Rector\Tests\ChangesReporting\Output\Factory\JsonOutputFactoryTest
 */
final class JsonOutputFactory
{
    public static function create(ProcessResult $processResult, Configuration $configuration): string
    {
        $errorsJson = ['totals' => ['changed_files' => $processResult->getTotalChanged()]];
        // We need onlyWithChanges: false to include all file diffs
        $fileDiffs = $processResult->getFileDiffs(\false);
        ksort($fileDiffs);
        foreach ($fileDiffs as $fileDiff) {
            $filePath = $configuration->isReportingWithRealPath() ? $fileDiff->getAbsoluteFilePath() ?? '' : $fileDiff->getRelativeFilePath();
            if ($configuration->shouldShowDiffs() && $fileDiff->getDiff() !== '') {
                $errorsJson[Bridge::FILE_DIFFS][] = ['file' => $filePath, 'diff' => $fileDiff->getDiff(), 'applied_rectors' => $fileDiff->getRectorClasses()];
            }
            // for Rector CI
            $errorsJson['changed_files'][] = $filePath;
        }
        $systemErrors = $processResult->getSystemErrors();
        $errorsJson['totals']['errors'] = count($systemErrors);
        $errorsData = self::createErrorsData($systemErrors, $configuration->isReportingWithRealPath());
        if ($errorsData !== []) {
            $errorsJson['errors'] = $errorsData;
        }
        return Json::encode($errorsJson, \true);
    }
    /**
     * @param SystemError[] $errors
     * @return mixed[]
     */
    private static function createErrorsData(array $errors, bool $absoluteFilePath): array
    {
        $errorsData = [];
        foreach ($errors as $error) {
            $errorDataJson = ['message' => $error->getMessage(), 'file' => $absoluteFilePath ? $error->getAbsoluteFilePath() : $error->getRelativeFilePath()];
            if ($error->getRectorClass() !== null) {
                $errorDataJson['caused_by'] = $error->getRectorClass();
            }
            if ($error->getLine() !== null) {
                $errorDataJson['line'] = $error->getLine();
            }
            $errorsData[] = $errorDataJson;
        }
        return $errorsData;
    }
}
