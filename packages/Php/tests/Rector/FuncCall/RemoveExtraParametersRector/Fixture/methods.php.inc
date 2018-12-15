<?php

namespace Rector\Php\Tests\Rector\FuncCall\RemoveExtraParametersRector\Fixture;

final class MethodExtraArgument
{
    public function run()
    {
        $this->perform(1);
        $this->perform(1, 2);
    }

    private function perform($value)
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
    }

    private function perform($value)
    {
    }
}

?>

