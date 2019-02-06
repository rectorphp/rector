<?php

namespace Rector\NetteToSymfony\Tests\Rector\MethodCall\FromRequestGetParameterToAttributesGetRector\Fixture;

use Rector\NetteToSymfony\Tests\Rector\MethodCall\FromRequestGetParameterToAttributesGetRector\Source\NetteRequest;

final class SomeController
{
    public static function someAction(NetteRequest $request)
    {
        $value = $request->getParameter('abz');
    }
}

?>
-----
<?php

namespace Rector\NetteToSymfony\Tests\Rector\MethodCall\FromRequestGetParameterToAttributesGetRector\Fixture;

use Rector\NetteToSymfony\Tests\Rector\MethodCall\FromRequestGetParameterToAttributesGetRector\Source\NetteRequest;

final class SomeController
{
    public static function someAction(NetteRequest $request)
    {
        $value = $request->attributes->get('abz');
    }
}

?>
