<?php

namespace Rector\CodeQuality\Tests\Rector\FunctionLike\RemoveAlwaysTrueConditionSetInConstructorRector\Fixture;

use stdClass;

final class MultipleLinesInCallable
{
    private $value;

    public function __construct(stdClass $value)
    {
        $this->value = $value;
    }

    public function go()
    {
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

final class MultipleLinesInCallable
{
    private $value;

    public function __construct(stdClass $value)
    {
        $this->value = $value;
    }

    public function go()
    {
        $callable = function () {
            $maybe = 1;
            return 'she said ' . $maybe;
        };
    }
}

?>
