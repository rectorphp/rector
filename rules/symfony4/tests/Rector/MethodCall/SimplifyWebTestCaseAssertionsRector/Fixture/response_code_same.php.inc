<?php

namespace Rector\Symfony4\Tests\Rector\MethodCall\SimplifyWebTestCaseAssertionsRector\Fixture;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ResponseCodeSame extends WebTestCase
{
    public function test()
    {
        $this->assertSame(362, $client->getResponse()->getStatusCode());
    }
}

?>
-----
<?php

namespace Rector\Symfony4\Tests\Rector\MethodCall\SimplifyWebTestCaseAssertionsRector\Fixture;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ResponseCodeSame extends WebTestCase
{
    public function test()
    {
        $this->assertResponseStatusCodeSame(362);
    }
}

?>
