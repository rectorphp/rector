<?php

namespace Rector\CodeQuality\Tests\Rector\FunctionLike\RemoveAlwaysTrueConditionSetInConstructorRector\Fixture;

final class ConstantStringWithValue
{
    private $value;

    public function __construct()
    {
        $this->value = 'hi';
    }

    public function go()
    {
        if ($this->value) {
            return 'yes';
        }
    }
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\FunctionLike\RemoveAlwaysTrueConditionSetInConstructorRector\Fixture;

final class ConstantStringWithValue
{
    private $value;

    public function __construct()
    {
        $this->value = 'hi';
    }

    public function go()
    {
        return 'yes';
    }
}

?>
