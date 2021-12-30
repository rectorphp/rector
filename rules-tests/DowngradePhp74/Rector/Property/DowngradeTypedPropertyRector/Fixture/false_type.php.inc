<?php

namespace Rector\Tests\DowngradePhp74\Rector\Property\DowngradeTypedPropertyRector\Fixture;

class Foo
{

    private Foo|Bar|false $prop;

}
?>
-----
<?php

namespace Rector\Tests\DowngradePhp74\Rector\Property\DowngradeTypedPropertyRector\Fixture;

class Foo
{

    /**
     * @var \Rector\Tests\DowngradePhp74\Rector\Property\DowngradeTypedPropertyRector\Fixture\Bar|\Rector\Tests\DowngradePhp74\Rector\Property\DowngradeTypedPropertyRector\Fixture\Foo|false
     */
    private $prop;

}
?>
