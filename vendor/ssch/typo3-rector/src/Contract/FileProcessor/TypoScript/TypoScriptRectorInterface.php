<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Contract\FileProcessor\TypoScript;

use RectorPrefix20220531\Helmich\TypoScriptParser\Parser\Traverser\Visitor;
use Rector\Core\Contract\Rector\RectorInterface;
interface TypoScriptRectorInterface extends \Rector\Core\Contract\Rector\RectorInterface, \RectorPrefix20220531\Helmich\TypoScriptParser\Parser\Traverser\Visitor
{
}
