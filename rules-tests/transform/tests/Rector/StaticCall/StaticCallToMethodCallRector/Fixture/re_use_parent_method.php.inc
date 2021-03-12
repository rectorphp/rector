<?php

namespace Rector\Transform\Tests\Rector\StaticCall\StaticCallToMethodCallRector\Fixture;

use Nette\Utils\FileSystem;
use Rector\Transform\Tests\Rector\StaticCall\StaticCallToMethodCallRector\Source\ClassWithFileSystemMethod;

class ReUseParentProperty extends ClassWithFileSystemMethod
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
use Rector\Transform\Tests\Rector\StaticCall\StaticCallToMethodCallRector\Source\ClassWithFileSystemMethod;

class ReUseParentProperty extends ClassWithFileSystemMethod
{
    public function run()
    {
        return $this->getSmartFileSystem()->dumpFile('file', 'content');
    }
}

?>
