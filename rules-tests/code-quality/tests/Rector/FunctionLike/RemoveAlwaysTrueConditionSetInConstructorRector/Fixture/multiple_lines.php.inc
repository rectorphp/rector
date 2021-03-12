<?php

namespace Rector\CodeQuality\Tests\Rector\FunctionLike\RemoveAlwaysTrueConditionSetInConstructorRector\Fixture;

use stdClass;

final class MultipleLines
{
    private $value;

    public function __construct(stdClass $value)
    {
        $this->value = $value;
    }

    public function go()
    {
        if ($this->value) {
            $maybe = 'yes';
            return 'she says ' . $maybe;
        }

        foreach ([1, 2, 3] as $number) {
            if ($number > 100) {
                return $number;
            }
        }
    }
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\FunctionLike\RemoveAlwaysTrueConditionSetInConstructorRector\Fixture;

use stdClass;

final class MultipleLines
{
    private $value;

    public function __construct(stdClass $value)
    {
        $this->value = $value;
    }

    public function go()
    {
        $maybe = 'yes';
        return 'she says ' . $maybe;
        foreach ([1, 2, 3] as $number) {
            if ($number > 100) {
                return $number;
            }
        }
    }
}

?>
