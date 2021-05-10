<?php

declare (strict_types=1);
namespace RectorPrefix20210510\Symplify\EasyTesting\Tests\MissingSkipPrefixResolver;

use RectorPrefix20210510\Symplify\EasyTesting\Finder\FixtureFinder;
use RectorPrefix20210510\Symplify\EasyTesting\HttpKernel\EasyTestingKernel;
use RectorPrefix20210510\Symplify\EasyTesting\MissplacedSkipPrefixResolver;
use RectorPrefix20210510\Symplify\PackageBuilder\Testing\AbstractKernelTestCase;
final class MissingSkipPrefixResolverTest extends AbstractKernelTestCase
{
    /**
     * @var MissplacedSkipPrefixResolver
     */
    private $missplacedSkipPrefixResolver;
    /**
     * @var FixtureFinder
     */
    private $fixtureFinder;
    protected function setUp() : void
    {
        $this->bootKernel(EasyTestingKernel::class);
        $this->missplacedSkipPrefixResolver = $this->getService(MissplacedSkipPrefixResolver::class);
        $this->fixtureFinder = $this->getService(FixtureFinder::class);
    }
    public function test() : void
    {
        $fileInfos = $this->fixtureFinder->find([__DIR__ . '/Fixture']);
        $invalidFileInfos = $this->missplacedSkipPrefixResolver->resolve($fileInfos);
        $this->assertCount(2, $invalidFileInfos);
    }
}
