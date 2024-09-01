<?php

declare (strict_types=1);
namespace Rector\ChangesReporting\Output;

use RectorPrefix202409\Nette\Utils\Json;
use Rector\ChangesReporting\Contract\Output\OutputFormatterInterface;
use Rector\Parallel\ValueObject\Bridge;
use Rector\ValueObject\Configuration;
use Rector\ValueObject\Error\SystemError;
use Rector\ValueObject\ProcessResult;
final class JsonOutputFormatter implements OutputFormatterInterface
{
    /**
     * @var string
     */
    public const NAME = 'json';
    public function getName() : string
    {
        return self::NAME;
    }
    public function report(ProcessResult $processResult, Configuration $configuration) : void
    {
        $errorsJson = ['totals' => ['changed_files' => \count($processResult->getFileDiffs())]];
        $fileDiffs = $processResult->getFileDiffs();
        \ksort($fileDiffs);
        foreach ($fileDiffs as $fileDiff) {
            $filePath = $configuration->isReportingWithRealPath() ? $fileDiff->getAbsoluteFilePath() ?? '' : $fileDiff->getRelativeFilePath();
            $errorsJson[Bridge::FILE_DIFFS][] = ['file' => $filePath, 'diff' => $fileDiff->getDiff(), 'applied_rectors' => $fileDiff->getRectorClasses()];
            // for Rector CI
            $errorsJson['changed_files'][] = $filePath;
        }
        $systemErrors = $processResult->getSystemErrors();
        $errorsJson['totals']['errors'] = \count($systemErrors);
        $errorsData = $this->createErrorsData($systemErrors, $configuration->isReportingWithRealPath());
        if ($errorsData !== []) {
            $errorsJson['errors'] = $errorsData;
        }
        $json = Json::encode($errorsJson, \true);
        echo $json . \PHP_EOL;
    }
    /**
     * @param SystemError[] $errors
     * @return mixed[]
     */
    private function createErrorsData(array $errors, bool $absoluteFilePath) : array
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
