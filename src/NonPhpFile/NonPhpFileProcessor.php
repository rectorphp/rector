<?php

declare (strict_types=1);
namespace Rector\Core\NonPhpFile;

use Rector\ChangesReporting\ValueObjectFactory\FileDiffFactory;
use Rector\Core\Contract\Processor\FileProcessorInterface;
use Rector\Core\Contract\Rector\NonPhpRectorInterface;
use Rector\Core\ValueObject\Application\File;
use Rector\Core\ValueObject\Configuration;
use Rector\Core\ValueObject\Error\SystemError;
use Rector\Core\ValueObject\Reporting\FileDiff;
use Rector\Core\ValueObject\StaticNonPhpFileSuffixes;
use Rector\Parallel\ValueObject\Bridge;
final class NonPhpFileProcessor implements \Rector\Core\Contract\Processor\FileProcessorInterface
{
    /**
     * @var NonPhpRectorInterface[]
     * @readonly
     */
    private $nonPhpRectors;
    /**
     * @readonly
     * @var \Rector\ChangesReporting\ValueObjectFactory\FileDiffFactory
     */
    private $fileDiffFactory;
    /**
     * @param NonPhpRectorInterface[] $nonPhpRectors
     */
    public function __construct(array $nonPhpRectors, \Rector\ChangesReporting\ValueObjectFactory\FileDiffFactory $fileDiffFactory)
    {
        $this->nonPhpRectors = $nonPhpRectors;
        $this->fileDiffFactory = $fileDiffFactory;
    }
    /**
     * @return array{system_errors: SystemError[], file_diffs: FileDiff[]}
     */
    public function process(\Rector\Core\ValueObject\Application\File $file, \Rector\Core\ValueObject\Configuration $configuration) : array
    {
        $systemErrorsAndFileDiffs = [\Rector\Parallel\ValueObject\Bridge::SYSTEM_ERRORS => [], \Rector\Parallel\ValueObject\Bridge::FILE_DIFFS => []];
        if ($this->nonPhpRectors === []) {
            return $systemErrorsAndFileDiffs;
        }
        $oldFileContent = $file->getFileContent();
        $newFileContent = $file->getFileContent();
        foreach ($this->nonPhpRectors as $nonPhpRector) {
            $newFileContent = $nonPhpRector->refactorFileContent($file->getFileContent());
            if ($oldFileContent === $newFileContent) {
                continue;
            }
            $file->changeFileContent($newFileContent);
        }
        if ($oldFileContent !== $newFileContent) {
            $fileDiff = $this->fileDiffFactory->createFileDiff($file, $oldFileContent, $newFileContent);
            $systemErrorsAndFileDiffs[\Rector\Parallel\ValueObject\Bridge::FILE_DIFFS][] = $fileDiff;
        }
        return $systemErrorsAndFileDiffs;
    }
    public function supports(\Rector\Core\ValueObject\Application\File $file, \Rector\Core\ValueObject\Configuration $configuration) : bool
    {
        $smartFileInfo = $file->getSmartFileInfo();
        // early assign to variable for increase performance
        // @see https://3v4l.org/FM3vY#focus=8.0.7 vs https://3v4l.org/JZW7b#focus=8.0.7
        $pathname = $smartFileInfo->getPathname();
        // bug in path extension
        foreach ($this->getSupportedFileExtensions() as $fileExtension) {
            if (\substr_compare($pathname, '.' . $fileExtension, -\strlen('.' . $fileExtension)) === 0) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * @return string[]
     */
    public function getSupportedFileExtensions() : array
    {
        return \Rector\Core\ValueObject\StaticNonPhpFileSuffixes::SUFFIXES;
    }
}
