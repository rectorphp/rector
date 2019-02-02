<?php declare(strict_types=1);

namespace Rector\Exception\Application;

use Exception;
use Symplify\PackageBuilder\FileSystem\SmartFileInfo;
use Throwable;

final class FileProcessingException extends Exception
{
    public function __construct(SmartFileInfo $smartFileInfo, Throwable $throwable)
    {
        $message = sprintf(
            'Processing file "%s" failed. %s%s',
            $smartFileInfo->getRealPath(),
            PHP_EOL . PHP_EOL,
            $throwable
        );

        parent::__construct($message);
    }
}
