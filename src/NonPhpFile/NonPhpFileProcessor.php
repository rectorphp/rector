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
final class NonPhpFileProcessor implements FileProcessorInterface
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
    public function __construct(array $nonPhpRectors, FileDiffFactory $fileDiffFactory)
    {
        $this->nonPhpRectors = $nonPhpRectors;
        $this->fileDiffFactory = $fileDiffFactory;
    }
    /**
     * @return array{system_errors: SystemError[], file_diffs: FileDiff[]}
     */
    public function process(File $file, Configuration $configuration) : array
    {
        $systemErrorsAndFileDiffs = [Bridge::SYSTEM_ERRORS => [], Bridge::FILE_DIFFS => []];
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
            $systemErrorsAndFileDiffs[Bridge::FILE_DIFFS][] = $fileDiff;
        }
        return $systemErrorsAndFileDiffs;
    }
    public function supports(File $file, Configuration $configuration) : bool
    {
        // early assign to variable for increase performance
        // @see https://3v4l.org/FM3vY#focus=8.0.7 vs https://3v4l.org/JZW7b#focus=8.0.7
        $filePath = $file->getFilePath();
        // bug in path extension
        foreach ($this->getSupportedFileExtensions() as $fileExtension) {
            if (\substr_compare($filePath, '.' . $fileExtension, -\strlen('.' . $fileExtension)) === 0) {
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
        return StaticNonPhpFileSuffixes::SUFFIXES;
    }
}
