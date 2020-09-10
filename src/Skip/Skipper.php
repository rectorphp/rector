<?php

declare(strict_types=1);

namespace Rector\Core\Skip;

use Symplify\SmartFileSystem\SmartFileInfo;

final class Skipper
{
    private $skip;

    /**
     * @param mixed[] $skip
     */
    public function __construct(array $skip = [])
    {
        $this->skip = $skip;
    }

    public function shouldSkipFileInfoAndRule(SmartFileInfo $smartFileInfo)
    {
        if ($this->skip === []) {
            return false;
        }

        return false;
    }
}
