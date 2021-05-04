<?php

namespace Rector\Tests\CodingStyle\Rector\FuncCall\CallUserFuncArrayToVariadicRector\Fixture;

use Rector\Tests\CodingStyle\Rector\FuncCall\CallUserFuncArrayToVariadicRector\Source\Redirector;

final class ArrayLocalMethodWithReference
{
    private $redirector;

    public function __construct()
    {
        $this->redirector = new Redirector();
    }

    public function run()
    {
        $args = \func_get_args();
        call_user_func_array([&$this->redirector, 'redirect'], $args);
    }
}

?>
-----
<?php

namespace Rector\Tests\CodingStyle\Rector\FuncCall\CallUserFuncArrayToVariadicRector\Fixture;

use Rector\Tests\CodingStyle\Rector\FuncCall\CallUserFuncArrayToVariadicRector\Source\Redirector;

final class ArrayLocalMethodWithReference
{
    private $redirector;

    public function __construct()
    {
        $this->redirector = new Redirector();
    }

    public function run()
    {
        $args = \func_get_args();
        $this->redirector->redirect(...$args);
    }
}

?>
