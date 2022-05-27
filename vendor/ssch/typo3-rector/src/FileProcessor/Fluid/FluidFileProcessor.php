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
final class FluidFileProcessor implements FileProcessorInterface
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
    public function __construct(array $fluidRectors, FileDiffFactory $fileDiffFactory)
    {
        $this->fluidRectors = $fluidRectors;
        $this->fileDiffFactory = $fileDiffFactory;
    }
    public function supports(File $file, Configuration $configuration) : bool
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
    public function process(File $file, Configuration $configuration) : array
    {
        $systemErrorsAndFileDiffs = [Bridge::SYSTEM_ERRORS => [], Bridge::FILE_DIFFS => []];
        $oldFileContents = $file->getFileContent();
        foreach ($this->fluidRectors as $fluidRector) {
            $fluidRector->transform($file);
        }
        if ($oldFileContents !== $file->getFileContent()) {
            $fileDiff = $this->fileDiffFactory->createFileDiff($file, $oldFileContents, $file->getFileContent());
            $systemErrorsAndFileDiffs[Bridge::FILE_DIFFS][] = $fileDiff;
        }
        return $systemErrorsAndFileDiffs;
    }
    public function getSupportedFileExtensions() : array
    {
        return ['html', 'xml', 'txt'];
    }
}
