<?php

namespace Rector\Transform\Tests\Rector\FuncCall\ArgumentFuncCallToMethodCallRector\Fixture;

class View
{
    public function action()
    {
        $template = view('template.blade');
        $viewFactory = view();
    }
}

?>
-----
<?php

namespace Rector\Transform\Tests\Rector\FuncCall\ArgumentFuncCallToMethodCallRector\Fixture;

class View
{
    private \Illuminate\Contracts\View\Factory $viewFactory;
    public function __construct(\Illuminate\Contracts\View\Factory $viewFactory)
    {
        $this->viewFactory = $viewFactory;
    }
    public function action()
    {
        $template = $this->viewFactory->make('template.blade');
        $viewFactory = $this->viewFactory;
    }
}

?>
