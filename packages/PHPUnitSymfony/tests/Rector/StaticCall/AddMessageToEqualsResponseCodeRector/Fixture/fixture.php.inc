<?php

namespace Rector\PHPUnitSymfony\Tests\Rector\StaticCall\AddMessageToEqualsResponseCodeRector\Fixture;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

final class SomeClassTest extends TestCase
{
    public function test(Response $response)
    {
        $this->assertEquals(
            Response::HTTP_NO_CONTENT,
            $response->getStatusCode()
        );

        $anotherName = new Response();
        $this->assertEquals(
            Response::HTTP_NO_CONTENT,
            $anotherName->getStatusCode()
        );
    }
}

?>
-----
<?php

namespace Rector\PHPUnitSymfony\Tests\Rector\StaticCall\AddMessageToEqualsResponseCodeRector\Fixture;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

final class SomeClassTest extends TestCase
{
    public function test(Response $response)
    {
        $this->assertEquals(
            Response::HTTP_NO_CONTENT,
            $response->getStatusCode(),
            $response->getContent()
        );

        $anotherName = new Response();
        $this->assertEquals(
            Response::HTTP_NO_CONTENT,
            $anotherName->getStatusCode(),
            $anotherName->getContent()
        );
    }
}

?>
