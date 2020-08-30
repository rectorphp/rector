<?php

namespace Rector\Transform\Tests\Rector\FuncCall\FuncCallToMethodCallRector\Fixture;

use function view;

class AnotherView
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

namespace Rector\Transform\Tests\Rector\FuncCall\FuncCallToMethodCallRector\Fixture;

use function view;

class AnotherView
{
    private \Namespaced\SomeRenderer $someRenderer;
    public function __construct(\Namespaced\SomeRenderer $someRenderer)
    {
        $this->someRenderer = $someRenderer;
    }
    public function someMethod()
    {
        $this->someRenderer->render('template', []);
        $this->someRenderer->render('template', []);
    }
}

?>
