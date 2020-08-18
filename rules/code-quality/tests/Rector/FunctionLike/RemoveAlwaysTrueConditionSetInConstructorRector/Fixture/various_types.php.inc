<?php

namespace Rector\CodeQuality\Tests\Rector\FunctionLike\RemoveAlwaysTrueConditionSetInConstructorRector\Fixture;

final class VariousTypes
{
    private $value;

    public function __construct()
    {
        $this->value = 15;
    }

    public function go()
    {
        $this->value = 'hi';

        if ($this->value) {
            return 'yes';
        }
    }
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\FunctionLike\RemoveAlwaysTrueConditionSetInConstructorRector\Fixture;

final class VariousTypes
{
    private $value;

    public function __construct()
    {
        $this->value = 15;
    }

    public function go()
    {
        $this->value = 'hi';

        return 'yes';
    }
}

?>
