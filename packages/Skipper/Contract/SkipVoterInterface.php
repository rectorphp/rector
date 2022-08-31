<?php

declare (strict_types=1);
namespace Rector\Skipper\Contract;

use Symplify\SmartFileSystem\SmartFileInfo;
interface SkipVoterInterface
{
    /**
     * @param string|object $element
     */
    public function match($element) : bool;
    /**
     * @param string|object $element
     * @param string|\Symplify\SmartFileSystem\SmartFileInfo $file
     */
    public function shouldSkip($element, $file) : bool;
}
