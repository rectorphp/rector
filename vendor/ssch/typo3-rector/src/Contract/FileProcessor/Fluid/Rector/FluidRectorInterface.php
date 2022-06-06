<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Ssch\TYPO3Rector\Contract\FileProcessor\Fluid\Rector;

use RectorPrefix20220606\Rector\Core\Contract\Rector\RectorInterface;
use RectorPrefix20220606\Rector\Core\ValueObject\Application\File;
interface FluidRectorInterface extends RectorInterface
{
    public function transform(File $file) : void;
}
