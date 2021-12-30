<?php

namespace Rector\Tests\DowngradePhp80\Rector\Property\DowngradeUnionTypeTypedPropertyRector\Fixture;

class Foo
{

    private Foo|Bar|false $prop;

}
?>
-----
<?php

namespace Rector\Tests\DowngradePhp80\Rector\Property\DowngradeUnionTypeTypedPropertyRector\Fixture;

class Foo
{

    /**
     * @var \Rector\Tests\DowngradePhp80\Rector\Property\DowngradeUnionTypeTypedPropertyRector\Fixture\Bar|\Rector\Tests\DowngradePhp80\Rector\Property\DowngradeUnionTypeTypedPropertyRector\Fixture\Foo|false
     */
    private $prop;

}
?>
