<?php

declare (strict_types=1);
namespace Rector\Core\Error;

use PHPStan\AnalysedCodeException;
final class ExceptionCorrector
{
    public function getAutoloadExceptionMessageAndAddLocation(AnalysedCodeException $analysedCodeException) : string
    {
        return \sprintf('Analyze error: "%s". Include your files in "$rectorConfig->autoloadPaths([...]);" or "$rectorConfig->bootstrapFiles([...]);" in "rector.php" config.%sSee https://github.com/rectorphp/rector#configuration', $analysedCodeException->getMessage(), \PHP_EOL);
    }
}
