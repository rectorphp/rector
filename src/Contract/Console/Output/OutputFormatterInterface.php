<?php

declare(strict_types=1);

namespace Rector\Contract\Console\Output;

use Rector\Application\ErrorAndDiffCollector;

interface OutputFormatterInterface
{
    public function getName(): string;

    public function report(ErrorAndDiffCollector $errorAndDiffCollector): void;
}
