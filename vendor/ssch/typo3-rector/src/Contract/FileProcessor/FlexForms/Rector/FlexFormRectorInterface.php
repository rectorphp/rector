<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Contract\FileProcessor\FlexForms\Rector;

use DOMDocument;
use Rector\Core\Contract\Rector\RectorInterface;
interface FlexFormRectorInterface extends \Rector\Core\Contract\Rector\RectorInterface
{
    /**
     * @param \DOMDocument $domDocument
     */
    public function transform($domDocument) : bool;
}
