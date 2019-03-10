<?php

namespace Rector\Laravel\Tests\Rector\FuncCall\HelperFunctionToConstructorInjectionRector\Fixture;

class SomeBackController
{
    public function action()
    {
        return back('template.blade');
    }
}

?>
-----
<?php

namespace Rector\Laravel\Tests\Rector\FuncCall\HelperFunctionToConstructorInjectionRector\Fixture;

class SomeBackController
{
    /**
     * @var \Illuminate\Routing\UrlGenerator
     */
    private $urlGenerator;
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
