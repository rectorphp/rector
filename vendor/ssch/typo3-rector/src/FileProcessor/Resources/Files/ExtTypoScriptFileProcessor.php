<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\FileProcessor\Resources\Files;

use Rector\Core\Contract\Processor\FileProcessorInterface;
use Rector\Core\ValueObject\Application\File;
use Rector\Core\ValueObject\Configuration;
use Rector\Core\ValueObject\Error\SystemError;
use Rector\Core\ValueObject\Reporting\FileDiff;
use Rector\Parallel\ValueObject\Bridge;
use Ssch\TYPO3Rector\Contract\FileProcessor\Resources\FileRectorInterface;
final class ExtTypoScriptFileProcessor implements FileProcessorInterface
{
    /**
     * @var FileRectorInterface[]
     * @readonly
     */
    private $filesRector;
    /**
     * @param FileRectorInterface[] $filesRector
     */
    public function __construct(array $filesRector)
    {
        $this->filesRector = $filesRector;
    }
    public function supports(File $file, Configuration $configuration) : bool
    {
        return \true;
    }
    /**
     * @return array{system_errors: SystemError[], file_diffs: FileDiff[]}
     */
    public function process(File $file, Configuration $configuration) : array
    {
        foreach ($this->filesRector as $fileRector) {
            $fileRector->refactorFile($file);
        }
        // to keep parent contract with return values
        return [Bridge::SYSTEM_ERRORS => [], Bridge::FILE_DIFFS => []];
    }
    public function getSupportedFileExtensions() : array
    {
        return ['txt'];
    }
}
