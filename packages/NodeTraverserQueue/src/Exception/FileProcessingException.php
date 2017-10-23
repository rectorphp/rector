<?php declare(strict_types=1);

namespace Rector\NodeTraverserQueue\Exception;

use Exception;
use SplFileInfo;
use Throwable;

final class FileProcessingException extends Exception
{
    public function __construct(SplFileInfo $fileInfo, Throwable $throwable)
    {
        $message = sprintf(
            'Processing file "%s" failed. ' . PHP_EOL . PHP_EOL . '%s',
            $fileInfo->getRealPath(),
            $throwable->getMessage()
        );

        parent::__construct($message);
    }
}
