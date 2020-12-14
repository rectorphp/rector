<?php

namespace Rector\Symfony4\Tests\Rector\MethodCall\SimplifyWebTestCaseAssertionsRector\Fixture;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class SomeClass extends WebTestCase
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

namespace Rector\Symfony4\Tests\Rector\MethodCall\SimplifyWebTestCaseAssertionsRector\Fixture;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class SomeClass extends WebTestCase
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
