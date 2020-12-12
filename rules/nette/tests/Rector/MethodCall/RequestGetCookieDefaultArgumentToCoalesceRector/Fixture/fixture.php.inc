<?php

namespace Rector\Nette\Tests\Rector\MethodCall\RequestGetCookieDefaultArgumentToCoalesceRector\Fixture;

use Nette\Http\Request;

class Fixture
{
    public function run(Request $request)
    {
        return $request->getCookie('name', 'default');
    }
}

?>
-----
<?php

namespace Rector\Nette\Tests\Rector\MethodCall\RequestGetCookieDefaultArgumentToCoalesceRector\Fixture;

use Nette\Http\Request;

class Fixture
{
    public function run(Request $request)
    {
        return $request->getCookie('name') ?? 'default';
    }
}

?>
