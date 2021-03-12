<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Fixture;

final class StaticAnalysisBased
{
    private $count;

    public function setCount(int $count)
    {
        $this->count = $count;
    }
}

?>
-----
<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Fixture;

final class StaticAnalysisBased
{
    private ?int $count = null;

    public function setCount(int $count)
    {
        $this->count = $count;
    }
}

?>
