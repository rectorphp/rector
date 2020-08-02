<?php

namespace Rector\Injection\Tests\Rector\StaticCall\StaticCallToAnotherServiceConstructorInjectionRector\Fixture;

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

namespace Rector\Injection\Tests\Rector\StaticCall\StaticCallToAnotherServiceConstructorInjectionRector\Fixture;

use Nette\Utils\FileSystem;

class InstantMakeInStaticMethod
{
    public static function run()
    {
        return (new \Symplify\SmartFileSystem\SmartFileSystem())->dumpFile('file', 'content');
    }
}

?>
