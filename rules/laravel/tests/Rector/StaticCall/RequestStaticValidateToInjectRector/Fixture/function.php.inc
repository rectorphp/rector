<?php

namespace Rector\Laravel\Tests\Rector\StaticCall\RequestStaticValidateToInjectRector\Fixture;

class Function
{
    public function store()
    {
        $validatedData = request()->get('foo');
    }
}

?>
-----
<?php

namespace Rector\Laravel\Tests\Rector\StaticCall\RequestStaticValidateToInjectRector\Fixture;

class Function
{
    public function store(\Illuminate\Http\Request $request)
    {
        $validatedData = $request->get('foo');
    }
}

?>
