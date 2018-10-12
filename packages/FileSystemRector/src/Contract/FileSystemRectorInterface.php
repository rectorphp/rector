<?php declare(strict_types=1);

namespace Rector\FileSystemRector\Contract;

use Rector\Contract\Rector\RectorInterface;
use Symplify\PackageBuilder\FileSystem\SmartFileInfo;

interface FileSystemRectorInterface extends RectorInterface
{
    public function refactor(SmartFileInfo $smartFileInfo): void;
}
