<?php

namespace Rector\Symfony\Tests\Rector\BinaryOp\ResponseStatusCodeRector\Fixture;

class SomeController
{
    public function index()
    {
        $response = new \Symfony\Component\HttpFoundation\Response();
        $response->setStatusCode(200);

        if ($response->getStatusCode() === 200) {}
    }
}

?>
-----
<?php

namespace Rector\Symfony\Tests\Rector\BinaryOp\ResponseStatusCodeRector\Fixture;

class SomeController
{
    public function index()
    {
        $response = new \Symfony\Component\HttpFoundation\Response();
        $response->setStatusCode(\Symfony\Component\HttpFoundation\Response::HTTP_OK);

        if ($response->getStatusCode() === \Symfony\Component\HttpFoundation\Response::HTTP_OK) {}
    }
}

?>
