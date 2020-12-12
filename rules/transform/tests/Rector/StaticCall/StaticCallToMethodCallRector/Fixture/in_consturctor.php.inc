<?php

namespace Rector\Transform\Tests\Rector\StaticCall\StaticCallToMethodCallRector\Fixture;

use Nette\Utils\FileSystem;

class InConstructor
{
    public function __construct()
    {
        return FileSystem::write('file', 'content');
    }
}

?>
-----
<?php

namespace Rector\Transform\Tests\Rector\StaticCall\StaticCallToMethodCallRector\Fixture;

use Nette\Utils\FileSystem;

class InConstructor
{
    public function __construct(\Symplify\SmartFileSystem\SmartFileSystem $smartFileSystem)
    {
        return $smartFileSystem->dumpFile('file', 'content');
    }
}

?>
