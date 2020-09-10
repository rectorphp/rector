<?php

declare(strict_types=1);

namespace Rector\Core\Skip;

use Symplify\SmartFileSystem\SmartFileInfo;

final class Skipper
{
    /**
     * @var mixed[]
     */
    private $skip;

    /**
     * @param mixed[] $skip
     */
    public function __construct(array $skip = [])
    {
        $this->skip = $skip;
    }

    public function shouldSkipFileInfoAndRule(SmartFileInfo $smartFileInfo): bool
    {
        if ($this->skip === []) {
            return false;
        }

        return false;
    }
}
