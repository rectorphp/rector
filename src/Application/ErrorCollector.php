<?php declare(strict_types=1);

namespace Rector\Application;

use Rector\Contract\Rector\RectorInterface;
use Rector\NodeTypeResolver\FileSystem\CurrentFileInfoProvider;

final class ErrorCollector
{
    /**
     * @var CurrentFileInfoProvider
     */
    private $currentFileInfoProvider;

    /**
     * @var Error[]
     */
    private $errors = [];

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

    public function addErrorWithRectorMessage(RectorInterface $rector, string $message): void
    {
        $this->errors[] = new Error($this->currentFileInfoProvider->getSmartFileInfo(), $message, null, $rector);
    }
}
