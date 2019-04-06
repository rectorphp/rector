<?php

namespace Rector\Symfony\Tests\Rector\MethodCall\SimplifyWebTestCaseAssertionsRector\Fixture;

use Rector\Symfony\Tests\Rector\MethodCall\SimplifyWebTestCaseAssertionsRector\Source\FixtureWebTestCase;

final class SomeClass extends FixtureWebTestCase
{
    public function test()
    {
        $this->assertSame(200, $client->getResponse()->getStatusCode());
    }

    public function testUrl()
    {
        $this->assertSame(301, $client->getResponse()->getStatusCode());
        $this->assertSame('https://example.com', $client->getResponse()->headers->get('Location'));
    }

    public function testContains()
    {
        $this->assertContains('Hello World', $crawler->filter('h1')->text());
    }
}

?>
-----
<?php

namespace Rector\Symfony\Tests\Rector\MethodCall\SimplifyWebTestCaseAssertionsRector\Fixture;

use Rector\Symfony\Tests\Rector\MethodCall\SimplifyWebTestCaseAssertionsRector\Source\FixtureWebTestCase;

final class SomeClass extends FixtureWebTestCase
{
    public function test()
    {
        $this->assertResponseIsSuccessful();
    }

    public function testUrl()
    {
        $this->assertResponseRedirects('https://example.com', 301);
    }

    public function testContains()
    {
        $this->assertSelectorTextContains('h1', 'Hello World');
    }
}

?>
