<?php

namespace Rector\Laravel\Tests\Rector\FuncCall\HelperFuncCallToFacadeClassRector\Fixture;

class Fixture
{
    public function run()
    {
        return app('translator')->trans('value');
    }
}

?>
-----
<?php

namespace Rector\Laravel\Tests\Rector\FuncCall\HelperFuncCallToFacadeClassRector\Fixture;

class Fixture
{
    public function run()
    {
        return \Illuminate\Support\Facades\App::get('translator')->trans('value');
    }
}

?>
