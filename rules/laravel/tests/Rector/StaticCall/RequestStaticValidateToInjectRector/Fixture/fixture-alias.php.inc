<?php

namespace Rector\Laravel\Tests\Rector\StaticCall\RequestStaticValidateToInjectRector\Fixture;

use Request;

class Fixture-alias
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

use Request;

class Fixture-alias
{
    public function store(\Illuminate\Http\Request $request)
    {
        $validatedData = $request->validate(['some_attribute' => 'required']);
    }
}

?>
