<?php

namespace Rector\Tests\PHPUnitSymfony\Rector\StaticCall\AddMessageToEqualsResponseCodeRector\Fixture;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

final class MethodCallTest extends TestCase
{
    public function test()
    {
        $this->assertEquals(
            Response::HTTP_NO_CONTENT,
            $this->getResponse()->getStatusCode()
        );
    }

    private function getResponse(): Response
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\PHPUnitSymfony\Rector\StaticCall\AddMessageToEqualsResponseCodeRector\Fixture;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

final class MethodCallTest extends TestCase
{
    public function test()
    {
        $this->assertEquals(
            Response::HTTP_NO_CONTENT,
            $this->getResponse()->getStatusCode(),
            $this->getResponse()->getContent()
        );
    }

    private function getResponse(): Response
    {
    }
}

?>
