<?php

declare (strict_types=1);
namespace Rector\Nette\FileProcessor;

use Rector\ChangesReporting\ValueObjectFactory\FileDiffFactory;
use Rector\Core\Contract\Processor\FileProcessorInterface;
use Rector\Core\ValueObject\Application\File;
use Rector\Core\ValueObject\Configuration;
use Rector\Core\ValueObject\Error\SystemError;
use Rector\Core\ValueObject\Reporting\FileDiff;
use Rector\Nette\Contract\Rector\LatteRectorInterface;
use Rector\Parallel\ValueObject\Bridge;
final class LatteFileProcessor implements FileProcessorInterface
{
    /**
     * @var LatteRectorInterface[]
     * @readonly
     */
    private $latteRectors;
    /**
     * @readonly
     * @var \Rector\ChangesReporting\ValueObjectFactory\FileDiffFactory
     */
    private $fileDiffFactory;
    /**
     * @param LatteRectorInterface[] $latteRectors
     */
    public function __construct(array $latteRectors, FileDiffFactory $fileDiffFactory)
    {
        $this->latteRectors = $latteRectors;
        $this->fileDiffFactory = $fileDiffFactory;
    }
    /**
     * @return array{system_errors: SystemError[], file_diffs: FileDiff[]}
     */
    public function process(File $file, Configuration $configuration) : array
    {
        $systemErrorsAndFileDiffs = [Bridge::SYSTEM_ERRORS => [], Bridge::FILE_DIFFS => []];
        $oldFileContent = $file->getFileContent();
        $fileContent = $file->getFileContent();
        foreach ($this->latteRectors as $latteRector) {
            $fileContent = $latteRector->changeContent($fileContent);
        }
        $file->changeFileContent($fileContent);
        if ($oldFileContent === $fileContent) {
            return $systemErrorsAndFileDiffs;
        }
        $fileDiff = $this->fileDiffFactory->createFileDiff($file, $oldFileContent, $fileContent);
        $systemErrorsAndFileDiffs[Bridge::FILE_DIFFS][] = $fileDiff;
        return $systemErrorsAndFileDiffs;
    }
    public function supports(File $file, Configuration $configuration) : bool
    {
        $fileInfo = $file->getSmartFileInfo();
        return $fileInfo->hasSuffixes($this->getSupportedFileExtensions());
    }
    /**
     * @return string[]
     */
    public function getSupportedFileExtensions() : array
    {
        return ['latte'];
    }
}
