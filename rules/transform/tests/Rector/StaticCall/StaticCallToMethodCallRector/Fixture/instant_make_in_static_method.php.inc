<?php

namespace Rector\Transform\Tests\Rector\StaticCall\StaticCallToMethodCallRector\Fixture;

use Nette\Utils\FileSystem;

class InstantMakeInStaticMethod
{
    public static function run()
    {
        return FileSystem::write('file', 'content');
    }
}

?>
-----
<?php

namespace Rector\Transform\Tests\Rector\StaticCall\StaticCallToMethodCallRector\Fixture;

use Nette\Utils\FileSystem;

class InstantMakeInStaticMethod
{
    public static function run()
    {
        return (new \Symplify\SmartFileSystem\SmartFileSystem())->dumpFile('file', 'content');
    }
}

?>
