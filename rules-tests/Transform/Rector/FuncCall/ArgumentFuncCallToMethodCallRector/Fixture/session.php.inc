<?php

namespace Rector\Transform\Tests\Rector\FuncCall\ArgumentFuncCallToMethodCallRector\Fixture;

class Session
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

namespace Rector\Transform\Tests\Rector\FuncCall\ArgumentFuncCallToMethodCallRector\Fixture;

class Session
{
    public function __construct(private \Illuminate\Session\SessionManager $sessionManager)
    {
    }
    public function action()
    {
        $session = $this->sessionManager;
        $this->sessionManager->put(['key']);
        $this->sessionManager->get('key', 'value');
    }
}

?>
