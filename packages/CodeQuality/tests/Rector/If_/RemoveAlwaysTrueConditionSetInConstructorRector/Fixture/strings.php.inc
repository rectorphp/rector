<?php

namespace Rector\CodeQuality\Tests\Rector\If_\RemoveAlwaysTrueConditionSetInConstructorRector\Fixture;

final class TestingStrings
{
    private $value;
    private $smallValue;

    public function __construct()
    {
        $this->value = 'hi';
        $this->smallValue = '';
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

namespace Rector\CodeQuality\Tests\Rector\If_\RemoveAlwaysTrueConditionSetInConstructorRector\Fixture;

final class TestingStrings
{
    private $value;
    private $smallValue;

    public function __construct()
    {
        $this->value = 'hi';
        $this->smallValue = '';
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
