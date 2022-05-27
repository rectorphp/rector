<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Contract\FileProcessor\FlexForms\Rector;

use DOMDocument;
use Rector\Core\Contract\Rector\RectorInterface;
interface FlexFormRectorInterface extends RectorInterface
{
    public function transform(DOMDocument $domDocument) : bool;
}
