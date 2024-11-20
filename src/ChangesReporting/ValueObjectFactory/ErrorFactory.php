<?php

declare (strict_types=1);
namespace Rector\ChangesReporting\ValueObjectFactory;

use PHPStan\AnalysedCodeException;
use Rector\FileSystem\FilePathHelper;
use Rector\ValueObject\Error\SystemError;
final class ErrorFactory
{
    /**
     * @readonly
     */
    private FilePathHelper $filePathHelper;
    public function __construct(FilePathHelper $filePathHelper)
    {
        $this->filePathHelper = $filePathHelper;
    }
    public function createAutoloadError(AnalysedCodeException $analysedCodeException, string $filePath) : SystemError
    {
        $message = $this->createExceptionMessage($analysedCodeException);
        $relativeFilePath = $this->filePathHelper->relativePath($filePath);
        return new SystemError($message, $relativeFilePath);
    }
    private function createExceptionMessage(AnalysedCodeException $analysedCodeException) : string
    {
        return \sprintf('Analyze error: "%s". Include your files in "$rectorConfig->autoloadPaths([...]);" or "$rectorConfig->bootstrapFiles([...]);" in "rector.php" config.%sSee https://github.com/rectorphp/rector#configuration', $analysedCodeException->getMessage(), \PHP_EOL);
    }
}
