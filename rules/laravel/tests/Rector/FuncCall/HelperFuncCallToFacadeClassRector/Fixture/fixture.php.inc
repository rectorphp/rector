<?php

namespace Rector\Laravel\Tests\Rector\FuncCall\HelperFuncCallToFacadeClassRector\Fixture;

class SomeClass
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

class SomeClass
{
    public function run()
    {
        return \Illuminate\Support\Facades\App::get('translator')->trans('value');
    }
}

?>
