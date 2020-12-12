<?php

namespace Rector\Transform\Tests\Rector\FuncCall\ArgumentFuncCallToMethodCallRector\Fixture;

class Route
{
    public function action()
    {
        return route('template.blade');
    }
}

?>
-----
<?php

namespace Rector\Transform\Tests\Rector\FuncCall\ArgumentFuncCallToMethodCallRector\Fixture;

class Route
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
