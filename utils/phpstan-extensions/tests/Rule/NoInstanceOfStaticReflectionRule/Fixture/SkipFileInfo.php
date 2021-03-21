<?php

declare(strict_types=1);

namespace Rector\PHPStanExtensions\Tests\Rule\NoInstanceOfStaticReflectionRule\Fixture;

use Symplify\SmartFileSystem\SmartFileInfo;

final class SkipFileInfo
{
    public function check($object)
    {
        if ($object instanceof SmartFileInfo) {
            return true;
        }
    }
}
