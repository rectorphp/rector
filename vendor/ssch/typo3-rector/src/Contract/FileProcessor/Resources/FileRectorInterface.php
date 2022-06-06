<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Ssch\TYPO3Rector\Contract\FileProcessor\Resources;

use RectorPrefix20220606\Rector\Core\Contract\Rector\RectorInterface;
use RectorPrefix20220606\Rector\Core\ValueObject\Application\File;
interface FileRectorInterface extends RectorInterface
{
    public function refactorFile(File $file) : void;
}
