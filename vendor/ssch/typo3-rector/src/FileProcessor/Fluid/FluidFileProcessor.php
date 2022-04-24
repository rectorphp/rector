<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\FileProcessor\Fluid;

use Rector\ChangesReporting\ValueObjectFactory\FileDiffFactory;
use Rector\Core\Contract\Processor\FileProcessorInterface;
use Rector\Core\ValueObject\Application\File;
use Rector\Core\ValueObject\Configuration;
use Rector\Core\ValueObject\Error\SystemError;
use Rector\Core\ValueObject\Reporting\FileDiff;
use Rector\Parallel\ValueObject\Bridge;
use Ssch\TYPO3Rector\Contract\FileProcessor\Fluid\Rector\FluidRectorInterface;
/**
 * @see \Ssch\TYPO3Rector\Tests\FileProcessor\Fluid\FluidProcessorTest
 */
final class FluidFileProcessor implements \Rector\Core\Contract\Processor\FileProcessorInterface
{
    /**
     * @var FluidRectorInterface[]
     * @readonly
     */
    private $fluidRectors;
    /**
     * @readonly
     * @var \Rector\ChangesReporting\ValueObjectFactory\FileDiffFactory
     */
    private $fileDiffFactory;
    /**
     * @param FluidRectorInterface[] $fluidRectors
     */
    public function __construct(array $fluidRectors, \Rector\ChangesReporting\ValueObjectFactory\FileDiffFactory $fileDiffFactory)
    {
        $this->fluidRectors = $fluidRectors;
        $this->fileDiffFactory = $fileDiffFactory;
    }
    public function supports(\Rector\Core\ValueObject\Application\File $file, \Rector\Core\ValueObject\Configuration $configuration) : bool
    {
        if ([] === $this->fluidRectors) {
            return \false;
        }
        $smartFileInfo = $file->getSmartFileInfo();
        return \in_array($smartFileInfo->getExtension(), $this->getSupportedFileExtensions(), \true);
    }
    /**
     * @return array{system_errors: SystemError[], file_diffs: FileDiff[]}
     */
    public function process(\Rector\Core\ValueObject\Application\File $file, \Rector\Core\ValueObject\Configuration $configuration) : array
    {
        $systemErrorsAndFileDiffs = [\Rector\Parallel\ValueObject\Bridge::SYSTEM_ERRORS => [], \Rector\Parallel\ValueObject\Bridge::FILE_DIFFS => []];
        $oldFileContents = $file->getFileContent();
        foreach ($this->fluidRectors as $fluidRector) {
            $fluidRector->transform($file);
        }
        if ($oldFileContents !== $file->getFileContent()) {
            $fileDiff = $this->fileDiffFactory->createFileDiff($file, $oldFileContents, $file->getFileContent());
            $systemErrorsAndFileDiffs[\Rector\Parallel\ValueObject\Bridge::FILE_DIFFS][] = $fileDiff;
        }
        return $systemErrorsAndFileDiffs;
    }
    public function getSupportedFileExtensions() : array
    {
        return ['html', 'xml', 'txt'];
    }
}
