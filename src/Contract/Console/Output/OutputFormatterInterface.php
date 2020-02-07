<?php

declare(strict_types=1);

namespace Rector\Core\Contract\Console\Output;

use Rector\Core\Application\ErrorAndDiffCollector;

interface OutputFormatterInterface
{
    public function getName(): string;

    public function report(ErrorAndDiffCollector $errorAndDiffCollector): void;
}
