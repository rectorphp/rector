<?php

namespace Rector\Transform\Tests\Rector\StaticCall\StaticCallToMethodCallRector\Fixture;

use Nette\Utils\FileSystem;
use Rector\Transform\Tests\Rector\StaticCall\StaticCallToMethodCallRector\Source\ClassWithFileSystem;

class ReUseParentMethod extends ClassWithFileSystem
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
use Rector\Transform\Tests\Rector\StaticCall\StaticCallToMethodCallRector\Source\ClassWithFileSystem;

class ReUseParentMethod extends ClassWithFileSystem
{
    public function run()
    {
        return $this->smartFileSystemProperty->dumpFile('file', 'content');
    }
}

?>
