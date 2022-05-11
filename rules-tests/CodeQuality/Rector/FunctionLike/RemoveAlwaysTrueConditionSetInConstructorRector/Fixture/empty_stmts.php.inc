<?php

namespace Rector\Tests\CodeQuality\Rector\FunctionLike\RemoveAlwaysTrueConditionSetInConstructorRector\Fixture;

use stdClass;

final class EmptyStmts
{
    private $value;

    public function __construct(stdClass $value)
    {
        $this->value = $value;
    }

    public function go()
    {
        if ($this->value) {
        }
    }
}

?>
-----
<?php

namespace Rector\Tests\CodeQuality\Rector\FunctionLike\RemoveAlwaysTrueConditionSetInConstructorRector\Fixture;

use stdClass;

final class EmptyStmts
{
    private $value;

    public function __construct(stdClass $value)
    {
        $this->value = $value;
    }

    public function go()
    {
    }
}

?>
