<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Contract\FileProcessor\Fluid\Rector;

use Rector\Core\Contract\Rector\RectorInterface;
use Rector\Core\ValueObject\Application\File;
interface FluidRectorInterface extends RectorInterface
{
    public function transform(File $file) : void;
}
