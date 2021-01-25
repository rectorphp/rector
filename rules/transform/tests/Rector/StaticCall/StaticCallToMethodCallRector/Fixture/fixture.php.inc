<?php

namespace Rector\Transform\Tests\Rector\StaticCall\StaticCallToMethodCallRector\Fixture;

use Nette\Utils\FileSystem;

class SomeClass
{
    public function run()
    {
        return FileSystem::write('file', 'content');
    }
}

?>
-----
<?php

namespace Rector\Transform\Tests\Rector\StaticCall\StaticCallToMethodCallRector\Fixture;

use Nette\Utils\FileSystem;

class SomeClass
{
    public function __construct(private \Symplify\SmartFileSystem\SmartFileSystem $smartFileSystem)
    {
    }
    public function run()
    {
        return $this->smartFileSystem->dumpFile('file', 'content');
    }
}

?>
