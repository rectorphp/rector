<?php

namespace Rector\Laravel\Tests\Rector\FuncCall\HelperFunctionToConstructorInjectionRector\Fixture;

class SomeRouteController
{
    public function action()
    {
        return route('template.blade');
    }
}

?>
-----
<?php

namespace Rector\Laravel\Tests\Rector\FuncCall\HelperFunctionToConstructorInjectionRector\Fixture;

class SomeRouteController
{
    private \Illuminate\Routing\UrlGenerator $urlGenerator;
    public function __construct(\Illuminate\Routing\UrlGenerator $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }
    public function action()
    {
        return $this->urlGenerator->route('template.blade');
    }
}

?>
