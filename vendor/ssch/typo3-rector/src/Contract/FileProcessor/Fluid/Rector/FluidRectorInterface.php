<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Contract\FileProcessor\Fluid\Rector;

use Rector\Core\Contract\Rector\RectorInterface;
use Rector\Core\ValueObject\Application\File;
interface FluidRectorInterface extends \Rector\Core\Contract\Rector\RectorInterface
{
    /**
     * @param \Rector\Core\ValueObject\Application\File $file
     */
    public function transform($file) : void;
}
