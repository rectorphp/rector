<?php

namespace Rector\Transform\Tests\Rector\StaticCall\StaticCallToMethodCallRector\Fixture;

use Illuminate\Support\Facades\Response;

class ExampleController
{
    public function store()
    {
        return Response::view('example', ['new_example' => 123]);
    }
}

?>
-----
<?php

namespace Rector\Transform\Tests\Rector\StaticCall\StaticCallToMethodCallRector\Fixture;

use Illuminate\Support\Facades\Response;

class ExampleController
{
    public function __construct(private \Illuminate\Contracts\Routing\ResponseFactory $responseFactory)
    {
    }
    public function store()
    {
        return $this->responseFactory->view('example', ['new_example' => 123]);
    }
}

?>
