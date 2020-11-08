<?php

namespace Rector\DeadCode\Tests\Rector\Property\RemoveUnusedPrivatePropertyRector\Fixture;

class TestException extends \Exception
{
    private $previous;

    public function __construct(\Throwable $previous)
    {
        parent::__construct("Message", 0, $previous);
        $this->previous = $previous;
    }
}
?>
-----
<?php

namespace Rector\DeadCode\Tests\Rector\Property\RemoveUnusedPrivatePropertyRector\Fixture;

class TestException extends \Exception
{
    public function __construct(\Throwable $previous)
    {
        parent::__construct("Message", 0, $previous);
    }
}
?>
