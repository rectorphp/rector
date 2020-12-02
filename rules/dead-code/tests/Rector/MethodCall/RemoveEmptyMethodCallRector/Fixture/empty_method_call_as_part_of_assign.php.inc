<?php

namespace Rector\DeadCode\Tests\Rector\MethodCall\RemoveEmptyMethodCallRector\Fixture;

use Rector\DeadCode\Tests\Rector\MethodCall\RemoveEmptyMethodCallRector\Source\EmptyMethod;

final class EmptyMethodCallAsPartOfAssign
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
        $var = $this->emptyMethod->run();

        return false;
    }
}

?>
-----
<?php

namespace Rector\DeadCode\Tests\Rector\MethodCall\RemoveEmptyMethodCallRector\Fixture;

use Rector\DeadCode\Tests\Rector\MethodCall\RemoveEmptyMethodCallRector\Source\EmptyMethod;

final class EmptyMethodCallAsPartOfAssign
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
        $var = false;

        return false;
    }
}

?>
