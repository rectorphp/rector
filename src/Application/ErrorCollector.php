<?php declare(strict_types=1);

namespace Rector\Application;

use Rector\NodeTypeResolver\FileSystem\CurrentFileInfoProvider;

final class ErrorCollector
{
    /**
     * @var Error[]
     */
    private $errors = [];

    /**
     * @var CurrentFileInfoProvider
     */
    private $currentFileInfoProvider;

    public function __construct(CurrentFileInfoProvider $currentFileInfoProvider)
    {
        $this->currentFileInfoProvider = $currentFileInfoProvider;
    }

    public function addError(Error $error): void
    {
        $this->errors[] = $error;
    }

    /**
     * @return Error[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    public function addErrorWithRectorMessage(string $rectorClass, string $message): void
    {
        $this->errors[] = new Error($this->currentFileInfoProvider->getSmartFileInfo(), $message, null, $rectorClass);
    }
}
