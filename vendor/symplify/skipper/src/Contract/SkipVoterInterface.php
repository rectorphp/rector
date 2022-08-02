<?php

declare (strict_types=1);
namespace RectorPrefix202208\Symplify\Skipper\Contract;

use RectorPrefix202208\Symplify\SmartFileSystem\SmartFileInfo;
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
