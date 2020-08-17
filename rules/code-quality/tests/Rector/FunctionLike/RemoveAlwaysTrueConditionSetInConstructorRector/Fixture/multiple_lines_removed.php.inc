<?php

namespace Rector\CodeQuality\Tests\Rector\FunctionLike\RemoveAlwaysTrueConditionSetInConstructorRector\Fixture;

use stdClass;

final class MultipleLinesRemoved
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
        $callable = function () {
            if ($this->value) {
                $maybe = 1;
                return 'she said ' . $maybe;
            }
        };
    }
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\FunctionLike\RemoveAlwaysTrueConditionSetInConstructorRector\Fixture;

use stdClass;

final class MultipleLinesRemoved
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
        $callable = function () {
            $maybe = 1;
            return 'she said ' . $maybe;
        };
    }
}

?>
