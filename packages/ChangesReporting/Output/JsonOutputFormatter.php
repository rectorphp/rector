<?php

declare (strict_types=1);
namespace Rector\ChangesReporting\Output;

use RectorPrefix20220418\Nette\Utils\Json;
use Rector\ChangesReporting\Annotation\RectorsChangelogResolver;
use Rector\ChangesReporting\Contract\Output\OutputFormatterInterface;
use Rector\Core\ValueObject\Configuration;
use Rector\Core\ValueObject\Error\SystemError;
use Rector\Core\ValueObject\ProcessResult;
use Rector\Parallel\ValueObject\Bridge;
final class JsonOutputFormatter implements \Rector\ChangesReporting\Contract\Output\OutputFormatterInterface
{
    /**
     * @var string
     */
    public const NAME = 'json';
    /**
     * @readonly
     * @var \Rector\ChangesReporting\Annotation\RectorsChangelogResolver
     */
    private $rectorsChangelogResolver;
    public function __construct(\Rector\ChangesReporting\Annotation\RectorsChangelogResolver $rectorsChangelogResolver)
    {
        $this->rectorsChangelogResolver = $rectorsChangelogResolver;
    }
    public function getName() : string
    {
        return self::NAME;
    }
    public function report(\Rector\Core\ValueObject\ProcessResult $processResult, \Rector\Core\ValueObject\Configuration $configuration) : void
    {
        $errorsJson = ['totals' => ['changed_files' => \count($processResult->getFileDiffs()), 'removed_and_added_files_count' => $processResult->getRemovedAndAddedFilesCount(), 'removed_node_count' => $processResult->getRemovedNodeCount()]];
        $fileDiffs = $processResult->getFileDiffs();
        \ksort($fileDiffs);
        foreach ($fileDiffs as $fileDiff) {
            $relativeFilePath = $fileDiff->getRelativeFilePath();
            $appliedRectorsWithChangelog = $this->rectorsChangelogResolver->resolve($fileDiff->getRectorClasses());
            $errorsJson[\Rector\Parallel\ValueObject\Bridge::FILE_DIFFS][] = ['file' => $relativeFilePath, 'diff' => $fileDiff->getDiff(), 'applied_rectors' => $fileDiff->getRectorClasses(), 'applied_rectors_with_changelog' => $appliedRectorsWithChangelog];
            // for Rector CI
            $errorsJson['changed_files'][] = $relativeFilePath;
        }
        $errors = $processResult->getErrors();
        $errorsJson['totals']['errors'] = \count($errors);
        $errorsData = $this->createErrorsData($errors);
        if ($errorsData !== []) {
            $errorsJson['errors'] = $errorsData;
        }
        $json = \RectorPrefix20220418\Nette\Utils\Json::encode($errorsJson, \RectorPrefix20220418\Nette\Utils\Json::PRETTY);
        echo $json . \PHP_EOL;
    }
    /**
     * @param SystemError[] $errors
     * @return mixed[]
     */
    private function createErrorsData(array $errors) : array
    {
        $errorsData = [];
        foreach ($errors as $error) {
            $errorDataJson = ['message' => $error->getMessage(), 'file' => $error->getRelativeFilePath()];
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
