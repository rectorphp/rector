<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUnusedPrivateMethodParameterRector\Fixture;

final class InBetweenParameter
{
    private $value;
    private $value3;

    private function run($value, $value2, $value3)
    {
        $this->value = $value;
        $this->value3 = $value3;
    }

    public function execute()
    {
        $this->run('a', 'b', 'c');
    }
}

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUnusedPrivateMethodParameterRector\Fixture;

final class InBetweenParameter
{
    private $value;
    private $value3;

    private function run($value, $value3)
    {
        $this->value = $value;
        $this->value3 = $value3;
    }

    public function execute()
    {
        $this->run('a', 'c');
    }
}

?>
