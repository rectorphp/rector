<?php

namespace Rector\Tests\CodingStyle\Rector\ClassMethod\UnSpreadOperatorRector\Fixture;

final class Fixture
{
    public function run(...$args)
    {
    }

    public function execute(array $data)
    {
        $this->run(...$data);
    }
}

?>
-----
<?php

namespace Rector\Tests\CodingStyle\Rector\ClassMethod\UnSpreadOperatorRector\Fixture;

final class Fixture
{
    public function run(array $args)
    {
    }

    public function execute(array $data)
    {
        $this->run($data);
    }
}

?>
