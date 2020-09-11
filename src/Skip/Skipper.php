<?php

declare(strict_types=1);

namespace Rector\Core\Skip;

use Rector\Core\Rector\AbstractRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class Skipper
{
    /**
     * @var mixed[]
     */
    private $skip = [];

    /**
     * @param mixed[] $skip
     */
    public function __construct(array $skip = [])
    {
        $this->skip = $skip;
    }

    public function shouldSkipFileInfoAndRule(SmartFileInfo $smartFileInfo, AbstractRector $rector): bool
    {
        if ($this->skip === []) {
            return false;
        }

        $rectorClass = get_class($rector);
        if (! in_array($rectorClass, $this->skip, true)) {
            return false;
        }

        $locations = $this->skip[$rectorClass];
        if (! in_array($smartFileInfo->getPathName(), $locations, true)) {
            return false;
        }

        return true;
    }
}
