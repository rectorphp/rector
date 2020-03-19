<?php

declare(strict_types=1);

namespace Rector\ChangesReporting\Contract\Output;

use Rector\ChangesReporting\Application\ErrorAndDiffCollector;

interface OutputFormatterInterface
{
    public function getName(): string;

    public function report(ErrorAndDiffCollector $errorAndDiffCollector): void;
}
