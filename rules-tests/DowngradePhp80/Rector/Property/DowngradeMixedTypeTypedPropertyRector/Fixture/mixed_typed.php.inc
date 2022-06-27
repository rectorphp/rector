<?php

namespace Rector\Tests\DowngradePhp80\Rector\Property\DowngradeMixedTypeTypedPropertyRector\Fixture;

class MixedTyped
{
    public mixed $value;
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp80\Rector\Property\DowngradeMixedTypeTypedPropertyRector\Fixture;

class MixedTyped
{
    /**
     * @var mixed
     */
    public $value;
}

?>
