<?php

namespace Rector\CodeQuality\Tests\Rector\FunctionLike\RemoveAlwaysTrueConditionSetInConstructorRector\Fixture;

final class FixStaticArray
{
    private $value;

    public function __construct()
    {
        $this->value = [5];
    }

    public function go()
    {
        if ($this->value) {
            $maybe = 'yes';
            return 'she says ' . $maybe;
        }
    }
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\FunctionLike\RemoveAlwaysTrueConditionSetInConstructorRector\Fixture;

final class FixStaticArray
{
    private $value;

    public function __construct()
    {
        $this->value = [5];
    }

    public function go()
    {
        $maybe = 'yes';
        return 'she says ' . $maybe;
    }
}

?>
