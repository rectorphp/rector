<?php

namespace Rector\Php74\Tests\Rector\Property\TypedPropertyRector\FixtureSafeTypes;

final class CompleteStrictType
{
    private $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }
}

?>
-----
<?php

namespace Rector\Php74\Tests\Rector\Property\TypedPropertyRector\FixtureSafeTypes;

final class CompleteStrictType
{
    private string $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }
}

?>
