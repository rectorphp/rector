<?php

declare(strict_types=1);

namespace Rector\Scan;

use Rector\FileSystem\FilesFinder;
use Symfony\Component\Process\Process;

final class ErrorScanner
{
    /**
     * @var string[]
     */
    private $errors = [];

    /**
     * @var FilesFinder
     */
    private $filesFinder;

    public function __construct(FilesFinder $filesFinder)
    {
        $this->filesFinder = $filesFinder;
    }

    /**
     * @param string[] $source
     * @return string[]
     */
    public function scanSource(array $source): array
    {
        $this->setErrorHandler();

        $fileInfos = $this->filesFinder->findInDirectoriesAndFiles($source, ['php']);

        $commandLine = 'include "vendor/autoload.php";';

        foreach ($fileInfos as $fileInfo) {
            $currentCommandLine = $commandLine . PHP_EOL;
            $currentCommandLine .= sprintf('include "%s";',  $fileInfo->getRelativeFilePathFromCwd());

            $currentCommandLine = sprintf("php -r '%s'", $currentCommandLine);
            $process = Process::fromShellCommandline($currentCommandLine);
            $process->run();

            if ($process->isSuccessful()) {
                continue;
            }

            $this->errors[] = trim($process->getErrorOutput());
        }

        $this->restoreErrorHandler();

        return $this->errors;
    }

    public function shutdown_function(): void
    {
        $error = error_get_last();
        //check if it's a core/fatal error, otherwise it's a normal shutdown
        if ($error === null) {
            return;
        }

        if (! in_array(
            $error['type'],
            [
                E_ERROR,
                E_PARSE,
                E_CORE_ERROR,
                E_CORE_WARNING,
                E_COMPILE_ERROR,
                E_COMPILE_WARNING,
                E_RECOVERABLE_ERROR,
            ], true
        )) {
            return;
        }

        print_r($error);
    }

    /**
     * @see https://www.php.net/manual/en/function.set-error-handler.php
     * @see https://stackoverflow.com/a/36638910/1348344
     */
    private function setErrorHandler(): void
    {
        register_shutdown_function([$this, 'shutdown_function']);

        set_error_handler(function (int $num, string $error): void {
            $this->errors[] = $error;
        });
    }

    private function restoreErrorHandler(): void
    {
        restore_error_handler();
    }
}
