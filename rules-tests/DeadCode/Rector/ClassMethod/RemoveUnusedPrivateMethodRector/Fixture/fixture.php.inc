<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUnusedPrivateMethodRector\Fixture;

use Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUnusedPrivateMethodRector\Source\AnotherCaller;

final class SomeController
{
    /**
     * @var AnotherCaller
     */
    private $anotherCaller;

    public function __construct(AnotherCaller $anotherCaller)
    {
        $this->anotherCaller = $anotherCaller;
    }

    public function run()
    {
        $this->anotherCaller->skip();
        return 5;
    }

    private function skip()
    {
        return 10;
    }
}

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUnusedPrivateMethodRector\Fixture;

use Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUnusedPrivateMethodRector\Source\AnotherCaller;

final class SomeController
{
    /**
     * @var AnotherCaller
     */
    private $anotherCaller;

    public function __construct(AnotherCaller $anotherCaller)
    {
        $this->anotherCaller = $anotherCaller;
    }

    public function run()
    {
        $this->anotherCaller->skip();
        return 5;
    }
}

?>
