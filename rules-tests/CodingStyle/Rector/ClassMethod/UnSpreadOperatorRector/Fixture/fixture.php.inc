<?php

namespace Rector\Tests\CodingStyle\Rector\ClassMethod\UnSpreadOperatorRector\Fixture;

final class Fixture
{
    public function execute(array $data)
    {
        $this->run(...$data);
    }

    private function run(...$args)
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\CodingStyle\Rector\ClassMethod\UnSpreadOperatorRector\Fixture;

final class Fixture
{
    public function execute(array $data)
    {
        $this->run($data);
    }

    private function run(array $args = [])
    {
    }
}

?>
