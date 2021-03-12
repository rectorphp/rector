<?php

namespace Rector\Laravel\Tests\Rector\StaticCall\Redirect301ToPermanentRedirectRector\Fixture;

class Fixture
{
    public function run()
    {
        \Illuminate\Routing\Route::redirect('/foo', '/bar', 301);
    }
}

?>
-----
<?php

namespace Rector\Laravel\Tests\Rector\StaticCall\Redirect301ToPermanentRedirectRector\Fixture;

class Fixture
{
    public function run()
    {
        \Illuminate\Routing\Route::permanentRedirect('/foo', '/bar');
    }
}

?>
