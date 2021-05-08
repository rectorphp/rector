<?php

declare(strict_types=1);

namespace Rector\Core\Contract\Application;

use Rector\Core\ValueObject\Application\File;

interface ApplicationProgressBarInterface
{
    public function start(int $count): void;

    public function advance(File $file, string $phase): void;

    public function finish(): void;
}
