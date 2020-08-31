<?php

declare(strict_types=1);

namespace Rector\Transform\Tests\Rector\StaticCall\StaticCallToMethodCallRector\Source;

use Symplify\SmartFileSystem\SmartFileSystem;

abstract class ClassWithFileSystem
{
    /**
     * @var SmartFileSystem
     */
    public $smartFileSystemProperty;
}
