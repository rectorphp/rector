<?php

namespace Rector\Symfony\Tests\Rector\MethodCall\SimplifyWebTestCaseAssertionsRector\Fixture;

use Rector\Symfony\Tests\Rector\MethodCall\SimplifyWebTestCaseAssertionsRector\Source\FixtureWebTestCase;

final class ResponseCodeSame extends FixtureWebTestCase
{
    public function test()
    {
        $this->assertSame(362, $client->getResponse()->getStatusCode());
    }
}

?>
-----
<?php

namespace Rector\Symfony\Tests\Rector\MethodCall\SimplifyWebTestCaseAssertionsRector\Fixture;

use Rector\Symfony\Tests\Rector\MethodCall\SimplifyWebTestCaseAssertionsRector\Source\FixtureWebTestCase;

final class ResponseCodeSame extends FixtureWebTestCase
{
    public function test()
    {
        $this->assertResponseStatusCodeSame(362);
    }
}

?>
