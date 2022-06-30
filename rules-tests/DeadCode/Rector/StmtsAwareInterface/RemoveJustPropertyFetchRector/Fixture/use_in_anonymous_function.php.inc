<?php

namespace Rector\Tests\DeadCode\Rector\StmtsAwareInterface\RemoveJustPropertyFetchRector\Fixture;

final class SkipUseInStaticFunction
{
    private $id;

    public function run()
    {
        $id = $this->id;

        function () use ($id) {
            $id();
        };
    }
}

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\StmtsAwareInterface\RemoveJustPropertyFetchRector\Fixture;

final class SkipUseInStaticFunction
{
    private $id;

    public function run()
    {
        function () {
            $this->id();
        };
    }
}

?>
