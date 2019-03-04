<?php

namespace Rector\Laravel\Tests\Rector\StaticCall\RequestStaticValidateToInjectRector\Fixture;

use Illuminate\Http\Request;

class SomeClass
{
    public function store()
    {
        $validatedData = Request::validate(['some_attribute' => 'required']);
    }
}

?>
-----
<?php

namespace Rector\Laravel\Tests\Rector\StaticCall\RequestStaticValidateToInjectRector\Fixture;

use Illuminate\Http\Request;

class SomeClass
{
    public function store(\Illuminate\Http\Request $request)
    {
        $validatedData = $request->validate(['some_attribute' => 'required']);
    }
}

?>
