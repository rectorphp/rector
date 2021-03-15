<?php

declare(strict_types=1);

namespace Rector\FileSystemRector\Contract;

interface MovedFileInterface
{
    public function getOldPathname(): string;

    public function getNewPathname(): string;
}
