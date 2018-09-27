<?php declare(strict_types=1);

namespace Rector\Exception\Application;

use Exception;
use Symfony\Component\Finder\SplFileInfo;
use Throwable;
use function Safe\sprintf;

final class FileProcessingException extends Exception
{
    public function __construct(SplFileInfo $fileInfo, Throwable $throwable)
    {
        $message = sprintf(
            'Processing file "%s" failed. ' . PHP_EOL . PHP_EOL . '%s',
            $fileInfo->getRealPath(),
            $throwable
        );

        parent::__construct($message);
    }
}
