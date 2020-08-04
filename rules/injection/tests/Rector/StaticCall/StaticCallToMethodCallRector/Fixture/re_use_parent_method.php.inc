<?php

namespace Rector\Injection\Tests\Rector\StaticCall\StaticCallToMethodCallRector\Fixture;

use Nette\Utils\FileSystem;
use Rector\Injection\Tests\Rector\StaticCall\StaticCallToMethodCallRector\Source\ClassWithFileSystemMethod;

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

namespace Rector\Injection\Tests\Rector\StaticCall\StaticCallToMethodCallRector\Fixture;

use Nette\Utils\FileSystem;
use Rector\Injection\Tests\Rector\StaticCall\StaticCallToMethodCallRector\Source\ClassWithFileSystemMethod;

class ReUseParentProperty extends ClassWithFileSystemMethod
{
    public function run()
    {
        return $this->getSmartFileSystem()->dumpFile('file', 'content');
    }
}

?>
