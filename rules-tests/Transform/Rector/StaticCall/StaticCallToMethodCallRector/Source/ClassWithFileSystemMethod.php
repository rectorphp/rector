<?php

declare(strict_types=1);

namespace Rector\Tests\Transform\Rector\StaticCall\StaticCallToMethodCallRector\Source;

use Symplify\SmartFileSystem\SmartFileSystem;

abstract class ClassWithFileSystemMethod
{
    public function getSmartFileSystem(): SmartFileSystem
    {
        return new SmartFileSystem();
    }
}
