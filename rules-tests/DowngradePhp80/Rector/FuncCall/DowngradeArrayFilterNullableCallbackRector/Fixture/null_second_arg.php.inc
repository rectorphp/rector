<?php

namespace Rector\Tests\DowngradePhp80\Rector\FuncCall\DowngradeArrayFilterNullableCallbackRector\Fixture;

class NullSecondArg
{
    public function run(array $data)
    {
        array_filter($data, null);
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp80\Rector\FuncCall\DowngradeArrayFilterNullableCallbackRector\Fixture;

class NullSecondArg
{
    public function run(array $data)
    {
        array_filter($data);
    }
}

?>
