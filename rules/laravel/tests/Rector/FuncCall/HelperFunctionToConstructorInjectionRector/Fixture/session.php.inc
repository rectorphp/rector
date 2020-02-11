<?php

namespace Rector\Laravel\Tests\Rector\FuncCall\HelperFunctionToConstructorInjectionRector\Fixture;

class SomeSessionController
{
    public function action()
    {
        $session = session();
        session(['key']);
        session('key', 'value');
    }
}

?>
-----
<?php

namespace Rector\Laravel\Tests\Rector\FuncCall\HelperFunctionToConstructorInjectionRector\Fixture;

class SomeSessionController
{
    /**
     * @var \Illuminate\Session\SessionManager
     */
    private $sessionManager;
    public function __construct(\Illuminate\Session\SessionManager $sessionManager)
    {
        $this->sessionManager = $sessionManager;
    }
    public function action()
    {
        $session = $this->sessionManager;
        $this->sessionManager->put(['key']);
        $this->sessionManager->get('key', 'value');
    }
}

?>
