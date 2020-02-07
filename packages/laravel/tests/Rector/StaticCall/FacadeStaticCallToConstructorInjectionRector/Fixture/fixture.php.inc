<?php

namespace Rector\Laravel\Tests\Rector\StaticCall\FacadeStaticCallToConstructorInjectionRector\Fixture;

use Illuminate\Support\Facades\Response;
use Rector\Laravel\Tests\Rector\StaticCall\FacadeStaticCallToConstructorInjectionRector\Source\LaravelController;

class ExampleController extends LaravelController
{
    public function store()
    {
        return Response::view('example', ['new_example' => 123]);
    }
}

?>
-----
<?php

namespace Rector\Laravel\Tests\Rector\StaticCall\FacadeStaticCallToConstructorInjectionRector\Fixture;

use Illuminate\Support\Facades\Response;
use Rector\Laravel\Tests\Rector\StaticCall\FacadeStaticCallToConstructorInjectionRector\Source\LaravelController;

class ExampleController extends LaravelController
{
    /**
     * @var \Illuminate\Contracts\Routing\ResponseFactory
     */
    private $responseFactory;
    public function __construct(\Illuminate\Contracts\Routing\ResponseFactory $responseFactory)
    {
        $this->responseFactory = $responseFactory;
    }
    public function store()
    {
        return $this->responseFactory->view('example', ['new_example' => 123]);
    }
}

?>
