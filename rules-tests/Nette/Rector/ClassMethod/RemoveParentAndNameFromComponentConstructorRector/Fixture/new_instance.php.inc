<?php

namespace Rector\Tests\Nette\Rector\ClassMethod\RemoveParentAndNameFromComponentConstructorRector\Fixture;

use Rector\Tests\Nette\Rector\ClassMethod\RemoveParentAndNameFromComponentConstructorRector\Source\SomeControlWithConstructorParentAndName;

final class NewInstance
{
    public function run()
    {
        $someControlWithConstructorParentAndName = new SomeControlWithConstructorParentAndName(null, 'hey');
    }
}

?>
-----
<?php

namespace Rector\Tests\Nette\Rector\ClassMethod\RemoveParentAndNameFromComponentConstructorRector\Fixture;

use Rector\Tests\Nette\Rector\ClassMethod\RemoveParentAndNameFromComponentConstructorRector\Source\SomeControlWithConstructorParentAndName;

final class NewInstance
{
    public function run()
    {
        $someControlWithConstructorParentAndName = new SomeControlWithConstructorParentAndName();
    }
}

?>
