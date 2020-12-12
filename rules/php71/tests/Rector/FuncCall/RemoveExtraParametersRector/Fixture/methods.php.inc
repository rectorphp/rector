<?php

namespace Rector\Php71\Tests\Rector\FuncCall\RemoveExtraParametersRector\Fixture;

use Rector\Php71\Tests\Rector\FuncCall\RemoveExtraParametersRector\Source\SomeExternalClass;

final class MethodExtraArgument
{
    public function run()
    {
        $this->perform(1);
        $this->perform(1, 2);

        (new SomeExternalClass())->hide(1);
        (new SomeExternalClass())->hide(1, 2);

        SomeExternalClass::seek(1);
        SomeExternalClass::seek(1, 2);
    }

    private function perform($value)
    {
    }
}

?>
-----
<?php

namespace Rector\Php71\Tests\Rector\FuncCall\RemoveExtraParametersRector\Fixture;

use Rector\Php71\Tests\Rector\FuncCall\RemoveExtraParametersRector\Source\SomeExternalClass;

final class MethodExtraArgument
{
    public function run()
    {
        $this->perform(1);
        $this->perform(1);

        (new SomeExternalClass())->hide(1);
        (new SomeExternalClass())->hide(1);

        SomeExternalClass::seek(1);
        SomeExternalClass::seek(1);
    }

    private function perform($value)
    {
    }
}

?>
