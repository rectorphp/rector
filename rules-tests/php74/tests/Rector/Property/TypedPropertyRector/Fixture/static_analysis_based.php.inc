<?php

namespace Rector\Php74\Tests\Rector\Property\TypedPropertyRector\Fixture;

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

namespace Rector\Php74\Tests\Rector\Property\TypedPropertyRector\Fixture;

final class StaticAnalysisBased
{
    private ?int $count = null;

    public function setCount(int $count)
    {
        $this->count = $count;
    }
}

?>
