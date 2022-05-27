<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Contract\FileProcessor\TypoScript;

use Rector\Core\Contract\Rector\RectorInterface;
interface TypoScriptPostRectorInterface extends RectorInterface
{
    public function apply(string $typoScriptContent) : string;
}
