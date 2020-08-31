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
    private \Symplify\SmartFileSystem\SmartFileSystem $smartFileSystem;
    public function __construct(\Symplify\SmartFileSystem\SmartFileSystem $smartFileSystem)
    {
        $this->smartFileSystem = $smartFileSystem;
    }
    public function run()
    {
        return $this->smartFileSystem->dumpFile('file', 'content');
    }
}

?>
