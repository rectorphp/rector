<?php

namespace Rector\NetteToSymfony\Tests\Rector\MethodCall\FromRequestGetParameterToAttributesGetRector\Fixture;

use Nette\Application\Request;

final class SomeController
{
    public function someAction(Request $request)
    {
        $value = $request->getParameter('abz');
    }

    public function anotherAction(Request $appRequest)
    {
        $value = $appRequest->getParameter('abz');
    }
}

?>
-----
<?php

namespace Rector\NetteToSymfony\Tests\Rector\MethodCall\FromRequestGetParameterToAttributesGetRector\Fixture;

use Nette\Application\Request;

final class SomeController
{
    public function someAction(Request $request)
    {
        $value = $request->attributes->get('abz');
    }

    public function anotherAction(Request $appRequest)
    {
        $value = $appRequest->attributes->get('abz');
    }
}

?>
