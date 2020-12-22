<?php

namespace Rector\Symfony\Tests\Rector\BinaryOp\ResponseStatusCodeRector\Fixture;

class Fixture
{
    public function index()
    {
        $response = new \Symfony\Component\HttpFoundation\Response();
        $response->setStatusCode(200);

        if ($response->getStatusCode() === 200) {}
        if (200 === $response->getStatusCode()) {}
        if ($response->getStatusCode() > 200) {}
        if ($response->getStatusCode() < 200) {}
        if ($response->getStatusCode() != 200) {}
        if ($response->getStatusCode() !== 200) {}
        if ($response->getStatusCode() === 200 || $response->getStatusCode() === 201)  {}
        if ($response->getStatusCode() !== 999999) {}
    }
}

?>
-----
<?php

namespace Rector\Symfony\Tests\Rector\BinaryOp\ResponseStatusCodeRector\Fixture;

class Fixture
{
    public function index()
    {
        $response = new \Symfony\Component\HttpFoundation\Response();
        $response->setStatusCode(\Symfony\Component\HttpFoundation\Response::HTTP_OK);

        if ($response->getStatusCode() === \Symfony\Component\HttpFoundation\Response::HTTP_OK) {}
        if (\Symfony\Component\HttpFoundation\Response::HTTP_OK === $response->getStatusCode()) {}
        if ($response->getStatusCode() > \Symfony\Component\HttpFoundation\Response::HTTP_OK) {}
        if ($response->getStatusCode() < \Symfony\Component\HttpFoundation\Response::HTTP_OK) {}
        if ($response->getStatusCode() != \Symfony\Component\HttpFoundation\Response::HTTP_OK) {}
        if ($response->getStatusCode() !== \Symfony\Component\HttpFoundation\Response::HTTP_OK) {}
        if ($response->getStatusCode() === \Symfony\Component\HttpFoundation\Response::HTTP_OK || $response->getStatusCode() === \Symfony\Component\HttpFoundation\Response::HTTP_CREATED)  {}
        if ($response->getStatusCode() !== 999999) {}
    }
}

?>
