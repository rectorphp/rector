<?php

namespace Rector\NetteToSymfony\Tests\Rector\MethodCall\FromRequestGetParameterToAttributesGetRector\Fixture;

use Rector\NetteToSymfony\Tests\Rector\MethodCall\FromRequestGetParameterToAttributesGetRector\Source\NetteRequest;

final class SomeController
{
    public function someAction(NetteRequest $request)
    {
        $value = $request->getParameter('abz');
    }

    public function anotherAction(NetteRequest $appRequest)
    {
        $value = $appRequest->getParameter('abz');
    }
}

?>
-----
<?php

namespace Rector\NetteToSymfony\Tests\Rector\MethodCall\FromRequestGetParameterToAttributesGetRector\Fixture;

use Rector\NetteToSymfony\Tests\Rector\MethodCall\FromRequestGetParameterToAttributesGetRector\Source\NetteRequest;

final class SomeController
{
    public function someAction(NetteRequest $request)
    {
        $value = $request->attributes->get('abz');
    }

    public function anotherAction(NetteRequest $appRequest)
    {
        $value = $appRequest->attributes->get('abz');
    }
}

?>
