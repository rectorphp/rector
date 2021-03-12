<?php

namespace Rector\Tests\DeadCode\Rector\MethodCall\RemoveEmptyMethodCallRector\Fixture;

use Rector\Tests\DeadCode\Rector\MethodCall\RemoveEmptyMethodCallRector\Source\EmptyMethod;

final class EmptyMethodCallAsPartOfIf
{
    /**
     * @var EmptyMethod
     */
    private $emptyMethod;

    public function __construct(EmptyMethod $emptyMethod)
    {
        $this->emptyMethod = $emptyMethod;
    }

    public function run()
    {
        if ($this->emptyMethod->run()) {
            return true;
        }

        return false;
    }
}

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\MethodCall\RemoveEmptyMethodCallRector\Fixture;

use Rector\Tests\DeadCode\Rector\MethodCall\RemoveEmptyMethodCallRector\Source\EmptyMethod;

final class EmptyMethodCallAsPartOfIf
{
    /**
     * @var EmptyMethod
     */
    private $emptyMethod;

    public function __construct(EmptyMethod $emptyMethod)
    {
        $this->emptyMethod = $emptyMethod;
    }

    public function run()
    {
        if (false) {
            return true;
        }

        return false;
    }
}

?>
