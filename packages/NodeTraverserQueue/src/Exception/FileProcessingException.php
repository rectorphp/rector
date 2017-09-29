<?php declare(strict_types=1);

namespace Rector\NodeTraverserQueue\Exception;

use Exception;
use Nette\Utils\Strings;
use SplFileInfo;
use Throwable;

final class FileProcessingException extends Exception
{
    public function __construct(SplFileInfo $fileInfo, Throwable $throwable)
    {
        [$file, $line] = self::resolveLocalFileAndLine($throwable);

        $message = sprintf(
            'Processing file "%s" failed due to "%s" in %s on line %d.',
            $fileInfo->getRealPath(),
            $throwable->getMessage(),
            $file,
            $line
        );

        parent::__construct($message);
    }

    /**
     * @return mixed[]
     */
    private static function resolveLocalFileAndLine(Throwable $throwable): array
    {
        foreach ($throwable->getTrace() as $trace) {
            // skip exception not from this project
            if (Strings::contains($trace['file'], 'vendor')) {
                continue;
            }

            return [$trace['file'], $trace['line']];
        }

        return [$throwable->getTrace()[0]['file'], $throwable->getTrace()[0]['line']];
    }
}
