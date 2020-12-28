<?php

namespace Rector\CodingStyle\Tests\Rector\ClassMethod\UnSpreadOperatorRector\Fixture;

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

namespace Rector\CodingStyle\Tests\Rector\ClassMethod\UnSpreadOperatorRector\Fixture;

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
