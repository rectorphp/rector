<?php

namespace Rector\Laravel\Tests\Rector\FuncCall\HelperFunctionToConstructorInjectionRector\Fixture;

class SomeBackController
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

namespace Rector\Laravel\Tests\Rector\FuncCall\HelperFunctionToConstructorInjectionRector\Fixture;

class SomeBackController
{
    /**
     * @var \Illuminate\Routing\Redirector
     */
    private $redirector;
    public function __construct(\Illuminate\Routing\Redirector $redirector)
    {
        $this->redirector = $redirector;
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
