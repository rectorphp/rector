<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Ssch\TYPO3Rector\Contract\FileProcessor\TypoScript;

use RectorPrefix20220606\Rector\Core\Contract\Rector\RectorInterface;
interface TypoScriptPostRectorInterface extends RectorInterface
{
    public function apply(string $typoScriptContent) : string;
}
