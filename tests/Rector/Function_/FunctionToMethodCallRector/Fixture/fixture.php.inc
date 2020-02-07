<?php

namespace Rector\Core\Tests\Rector\Function_\FunctionToMethodCallRector\Fixture;

use function view;

class SomeClass
{
    public function someMethod()
    {
        \view('template', []);
        view('template', []);
    }
}

?>
-----
<?php

namespace Rector\Core\Tests\Rector\Function_\FunctionToMethodCallRector\Fixture;

use function view;

class SomeClass
{
    public function someMethod()
    {
        $this->render('template', []);
        $this->render('template', []);
    }
}

?>
