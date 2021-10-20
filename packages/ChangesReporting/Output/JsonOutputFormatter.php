<?php

declare (strict_types=1);
namespace Rector\ChangesReporting\Output;

use RectorPrefix20211020\Nette\Utils\Json;
use Rector\ChangesReporting\Annotation\RectorsChangelogResolver;
use Rector\ChangesReporting\Contract\Output\OutputFormatterInterface;
use Rector\Core\ValueObject\Configuration;
use Rector\Core\ValueObject\ProcessResult;
final class JsonOutputFormatter implements \Rector\ChangesReporting\Contract\Output\OutputFormatterInterface
{
    /**
     * @var string
     */
    public const NAME = 'json';
    /**
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
    /**
     * @param \Rector\Core\ValueObject\ProcessResult $processResult
     * @param \Rector\Core\ValueObject\Configuration $configuration
     */
    public function report($processResult, $configuration) : void
    {
        $errorsArray = ['meta' => ['config' => $configuration->getMainConfigFilePath()], 'totals' => ['changed_files' => \count($processResult->getFileDiffs()), 'removed_and_added_files_count' => $processResult->getRemovedAndAddedFilesCount(), 'removed_node_count' => $processResult->getRemovedNodeCount()]];
        $fileDiffs = $processResult->getFileDiffs();
        \ksort($fileDiffs);
        foreach ($fileDiffs as $fileDiff) {
            $relativeFilePath = $fileDiff->getRelativeFilePath();
            $appliedRectorsWithChangelog = $this->rectorsChangelogResolver->resolve($fileDiff->getRectorClasses());
            $errorsArray['file_diffs'][] = ['file' => $relativeFilePath, 'diff' => $fileDiff->getDiff(), 'applied_rectors' => $fileDiff->getRectorClasses(), 'applied_rectors_with_changelog' => $appliedRectorsWithChangelog];
            // for Rector CI
            $errorsArray['changed_files'][] = $relativeFilePath;
        }
        $errors = $processResult->getErrors();
        $errorsArray['totals']['errors'] = \count($errors);
        $errorsData = $this->createErrorsData($errors);
        if ($errorsData !== []) {
            $errorsArray['errors'] = $errorsData;
        }
        $json = \RectorPrefix20211020\Nette\Utils\Json::encode($errorsArray, \RectorPrefix20211020\Nette\Utils\Json::PRETTY);
        echo $json . \PHP_EOL;
    }
    /**
     * @param mixed[] $errors
     * @return mixed[]
     */
    private function createErrorsData(array $errors) : array
    {
        $errorsData = [];
        foreach ($errors as $error) {
            $errorData = ['message' => $error->getMessage(), 'file' => $error->getRelativeFilePath()];
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
