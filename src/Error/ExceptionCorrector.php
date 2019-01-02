<?php declare(strict_types=1);

namespace Rector\Error;

use PHPStan\AnalysedCodeException;
use Rector\Contract\Rector\RectorInterface;
use function Safe\sprintf;
use Throwable;

final class ExceptionCorrector
{
    public function matchRectorClass(Throwable $throwable): ?string
    {
        if (! isset($throwable->getTrace()[0])) {
            return null;
        }

        /** @var string $class */
        $class = $throwable->getTrace()[0]['class'];
        if (! is_a($class, RectorInterface::class, true)) {
            return null;
        }

        return $class;
    }

    public function getAutoloadExceptionMessageAndAddLocation(AnalysedCodeException $analysedCodeException): string
    {
        return sprintf(
            'Analyze error: "%s". Include your files in "parameters > autoload_paths".%sSee https://github.com/rectorphp/rector#extra-autoloading',
            $analysedCodeException->getMessage(),
            PHP_EOL
        );
    }
}
