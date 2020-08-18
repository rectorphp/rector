<?php

namespace Rector\CodeQuality\Tests\Rector\FunctionLike\RemoveAlwaysTrueConditionSetInConstructorRector\Fixture;

final class Numbers
{
    private $value;
    private $smallValue;

    public function __construct()
    {
        $this->value = 15;
        $this->smallValue = 0;
    }

    public function go()
    {
        if ($this->value) {
            return 'yes';
        }

        if ($this->smallValue) {
            return 'no';
        }
    }
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\FunctionLike\RemoveAlwaysTrueConditionSetInConstructorRector\Fixture;

final class Numbers
{
    private $value;
    private $smallValue;

    public function __construct()
    {
        $this->value = 15;
        $this->smallValue = 0;
    }

    public function go()
    {
        return 'yes';

        if ($this->smallValue) {
            return 'no';
        }
    }
}

?>
