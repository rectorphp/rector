<?php

namespace Rector\Tests\Rector\Function_\FunctionToMethodCallRector\Wrong;

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

namespace Rector\Tests\Rector\Function_\FunctionToMethodCallRector\Wrong;

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
