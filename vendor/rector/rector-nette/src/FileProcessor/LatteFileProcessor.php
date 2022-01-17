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
final class LatteFileProcessor implements \Rector\Core\Contract\Processor\FileProcessorInterface
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
    public function __construct(array $latteRectors, \Rector\ChangesReporting\ValueObjectFactory\FileDiffFactory $fileDiffFactory)
    {
        $this->latteRectors = $latteRectors;
        $this->fileDiffFactory = $fileDiffFactory;
    }
    /**
     * @return array{system_errors: SystemError[], file_diffs: FileDiff[]}
     */
    public function process(\Rector\Core\ValueObject\Application\File $file, \Rector\Core\ValueObject\Configuration $configuration) : array
    {
        $systemErrorsAndFileDiffs = [\Rector\Parallel\ValueObject\Bridge::SYSTEM_ERRORS => [], \Rector\Parallel\ValueObject\Bridge::FILE_DIFFS => []];
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
        $systemErrorsAndFileDiffs[\Rector\Parallel\ValueObject\Bridge::FILE_DIFFS][] = $fileDiff;
        return $systemErrorsAndFileDiffs;
    }
    public function supports(\Rector\Core\ValueObject\Application\File $file, \Rector\Core\ValueObject\Configuration $configuration) : bool
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
