<?php

namespace Rector\Transform\Tests\Rector\FuncCall\ArgumentFuncCallToMethodCallRector\Fixture;

class Back
{
    public function action()
    {
        return back();
    }

    public function actionWithParams()
    {
        return back(200);
    }
}

?>
-----
<?php

namespace Rector\Transform\Tests\Rector\FuncCall\ArgumentFuncCallToMethodCallRector\Fixture;

class Back
{
    public function __construct(private \Illuminate\Routing\Redirector $redirector)
    {
    }
    public function action()
    {
        return $this->redirector->back();
    }

    public function actionWithParams()
    {
        return $this->redirector->back(200);
    }
}

?>
