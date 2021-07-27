<?php

namespace Rector\Tests\DeadCode\Rector\If_\RemoveDeadInstanceOfRector\Fixture;

use stdClass;

class NonTypedPropertyFilledByConstruct
{
    /** @var stdClass */
    private $var;

    public function __construct(stdClass $var)
    {
        $this->var = $var;
    }

    public function go()
    {
        if (! $this->var instanceof stdClass) {
            echo 'var is not an stdClass';
            return;
        }

        echo 'success' . PHP_EOL;
    }
}

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\If_\RemoveDeadInstanceOfRector\Fixture;

use stdClass;

class NonTypedPropertyFilledByConstruct
{
    /** @var stdClass */
    private $var;

    public function __construct(stdClass $var)
    {
        $this->var = $var;
    }

    public function go()
    {
        echo 'success' . PHP_EOL;
    }
}

?>
