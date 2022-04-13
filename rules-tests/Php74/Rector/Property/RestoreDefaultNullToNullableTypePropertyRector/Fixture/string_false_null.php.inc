<?php

namespace Rector\Tests\Php74\Rector\Property\RestoreDefaultNullToNullableTypePropertyRector\Fixture;

class StringFalseNull
{
    public string|false|null $name;
}

?>
-----
<?php

namespace Rector\Tests\Php74\Rector\Property\RestoreDefaultNullToNullableTypePropertyRector\Fixture;

class StringFalseNull
{
    public string|false|null $name = null;
}

?>
