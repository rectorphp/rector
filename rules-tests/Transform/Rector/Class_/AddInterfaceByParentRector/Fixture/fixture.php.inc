<?php

namespace Rector\Tests\Transform\Rector\Class_\AddInterfaceByParentRector\Fixture;

use Rector\Tests\Transform\Rector\Class_\AddInterfaceByParentRector\Source\SomeParent;

class SomeClass extends SomeParent
{

}

?>
-----
<?php

namespace Rector\Tests\Transform\Rector\Class_\AddInterfaceByParentRector\Fixture;

use Rector\Tests\Transform\Rector\Class_\AddInterfaceByParentRector\Source\SomeParent;

class SomeClass extends SomeParent implements \Rector\Tests\Transform\Rector\Class_\AddInterfaceByParentRector\Source\SomeInterface
{

}

?>
