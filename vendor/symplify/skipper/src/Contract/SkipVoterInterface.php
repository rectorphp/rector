<?php

declare (strict_types=1);
namespace RectorPrefix20220608\Symplify\Skipper\Contract;

use RectorPrefix20220608\Symplify\SmartFileSystem\SmartFileInfo;
interface SkipVoterInterface
{
    /**
     * @param string|object $element
     */
    public function match($element) : bool;
    /**
     * @param string|object $element
     */
    public function shouldSkip($element, SmartFileInfo $smartFileInfo) : bool;
}
