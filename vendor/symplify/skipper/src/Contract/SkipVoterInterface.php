<?php

declare (strict_types=1);
namespace RectorPrefix20220527\Symplify\Skipper\Contract;

use Symplify\SmartFileSystem\SmartFileInfo;
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
