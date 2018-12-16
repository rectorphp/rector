<?php

namespace Rector\Php\Tests\Rector\FuncCall\RemoveExtraParametersRector\Fixture;

final class MethodExtraArgument
{
    public function run()
    {
        $this->perform(1);
        $this->perform(1, 2);

        (new ExternalClass())->hide(1);
        (new ExternalClass())->hide(1, 2);

        ExternalClass::seek(1);
        ExternalClass::seek(1, 2);
    }

    private function perform($value)
    {
    }
}

class ExternalClass
{
    public function hide($value)
    {

    }

    public static function seek($value)
    {

    }
}

?>
-----
<?php

namespace Rector\Php\Tests\Rector\FuncCall\RemoveExtraParametersRector\Fixture;

final class MethodExtraArgument
{
    public function run()
    {
        $this->perform(1);
        $this->perform(1);

        (new ExternalClass())->hide(1);
        (new ExternalClass())->hide(1);

        ExternalClass::seek(1);
        ExternalClass::seek(1);
    }

    private function perform($value)
    {
    }
}

class ExternalClass
{
    public function hide($value)
    {

    }

    public static function seek($value)
    {

    }
}

?>
