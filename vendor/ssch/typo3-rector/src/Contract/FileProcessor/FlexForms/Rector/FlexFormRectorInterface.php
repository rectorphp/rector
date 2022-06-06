<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Ssch\TYPO3Rector\Contract\FileProcessor\FlexForms\Rector;

use DOMDocument;
use RectorPrefix20220606\Rector\Core\Contract\Rector\RectorInterface;
interface FlexFormRectorInterface extends RectorInterface
{
    public function transform(DOMDocument $domDocument) : bool;
}
