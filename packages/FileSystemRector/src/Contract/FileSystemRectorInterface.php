<?php declare(strict_types=1);

namespace Rector\FileSystemRector\Contract;

use Rector\Contract\Rector\RectorInterface;
use Symfony\Component\Finder\SplFileInfo;

interface FileSystemRectorInterface extends RectorInterface
{
    public function refactor(SplFileInfo $fileInfo): void;
}
