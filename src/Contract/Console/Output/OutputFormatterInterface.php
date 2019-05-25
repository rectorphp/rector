<?php declare(strict_types=1);

namespace Rector\Contract\Console\Output;

use Rector\Application\Error;
use Rector\Reporting\FileDiff;

interface OutputFormatterInterface
{
    public function getName(): string;

    /**
     * @param FileDiff[] $fileDiffs
     */
    public function reportFileDiffs(array $fileDiffs): void;

    /**
     * @param Error[] $errors
     */
    public function reportErrors(array $errors): void;
}
