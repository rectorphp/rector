<?php

namespace spec\Rector\PhpSpecToPHPUnit\Tests\Rector\Variable\PhpSpecToPHPUnitRector\Fixture;

use JsonApiBundle\Paging\PageLinksGenerator;
use JsonApiBundle\Paging\Paginator;
use Neomerx\JsonApi\Document\Link;
use PhpSpec\ObjectBehavior;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class PageLinksGeneratorSpec
 */
class PageLinksGeneratorSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(PageLinksGenerator::class);
    }

    public function let(Router $router)
    {
        $this->beConstructedWith($router);
    }

    public function it_should_return_only_self_link_when_no_results(Router $router)
    {
        $paginator = new Paginator(
            10,
            0,
            1,
            0,
            'route_name'
        );

        $router->generate($paginator->getRouteName(), [], UrlGeneratorInterface::ABSOLUTE_URL)
            ->shouldBeCalled()->willReturn('http://api.foo.loc/bar/1.0.0/');

        $expectations = [
            'self' => new Link('http://api.foo.loc/bar/1.0.0/'),
            'first' => null,
            'prev' => null,
            'next' => null,
            'last' => null,
        ];

        $this->generateLinks($paginator)->shouldBeLike($expectations);
    }
}
?>
-----
<?php

namespace Tests\Rector\PhpSpecToPHPUnit\Tests\Rector\Variable\PhpSpecToPHPUnitRector\Fixture;

use JsonApiBundle\Paging\PageLinksGenerator;
use JsonApiBundle\Paging\Paginator;
use Neomerx\JsonApi\Document\Link;
use PhpSpec\ObjectBehavior;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class PageLinksGeneratorSpec
 */
class PageLinksGeneratorTest extends \PHPUnit\Framework\TestCase
{
    private \Rector\PhpSpecToPHPUnit\Tests\Rector\Variable\PhpSpecToPHPUnitRector\Fixture\PageLinksGenerator $pageLinksGenerator;
    private \PHPUnit\Framework\MockObject\MockObject|\Router $router;
    public function testInitializable()
    {
        $this->assertInstanceOf(PageLinksGenerator::class, $this->pageLinksGenerator);
    }

    protected function setUp()
    {
        $this->router = $this->createMock(Router::class);
        $this->pageLinksGenerator = new \Rector\PhpSpecToPHPUnit\Tests\Rector\Variable\PhpSpecToPHPUnitRector\Fixture\PageLinksGenerator($this->router);
    }

    public function testReturnOnlySelfLinkWhenNoResults()
    {
        $paginator = new Paginator(
            10,
            0,
            1,
            0,
            'route_name'
        );
        $this->router->expects($this->atLeastOnce())
            ->method('generate')->with($this->equalTo($paginator->getRouteName()))->willReturn('http://api.foo.loc/bar/1.0.0/');
        $expectations = [
            'self' => new Link('http://api.foo.loc/bar/1.0.0/'),
            'first' => null,
            'prev' => null,
            'next' => null,
            'last' => null,
        ];
        $this->pageLinksGenerator->generateLinks($paginator)->shouldBeLike($expectations);
    }
}
?>
