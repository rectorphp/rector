<?php

namespace Rector\Nette\Tests\Rector\ClassMethod\RemoveParentAndNameFromComponentConstructorRector\Fixture;

use Rector\Nette\Tests\Rector\ClassMethod\RemoveParentAndNameFromComponentConstructorRector\Source\SomeControlWithConstructorParentAndName;

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

namespace Rector\Nette\Tests\Rector\ClassMethod\RemoveParentAndNameFromComponentConstructorRector\Fixture;

use Rector\Nette\Tests\Rector\ClassMethod\RemoveParentAndNameFromComponentConstructorRector\Source\SomeControlWithConstructorParentAndName;

final class NewInstance
{
    public function run()
    {
        $someControlWithConstructorParentAndName = new SomeControlWithConstructorParentAndName();
    }
}

?>
