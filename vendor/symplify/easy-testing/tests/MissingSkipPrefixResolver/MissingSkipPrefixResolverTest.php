<?php

declare (strict_types=1);
namespace RectorPrefix20210511\Symplify\EasyTesting\Tests\MissingSkipPrefixResolver;

use RectorPrefix20210511\Symplify\EasyTesting\Finder\FixtureFinder;
use RectorPrefix20210511\Symplify\EasyTesting\HttpKernel\EasyTestingKernel;
use RectorPrefix20210511\Symplify\EasyTesting\MissplacedSkipPrefixResolver;
use RectorPrefix20210511\Symplify\PackageBuilder\Testing\AbstractKernelTestCase;
final class MissingSkipPrefixResolverTest extends \RectorPrefix20210511\Symplify\PackageBuilder\Testing\AbstractKernelTestCase
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
        $this->bootKernel(\RectorPrefix20210511\Symplify\EasyTesting\HttpKernel\EasyTestingKernel::class);
        $this->missplacedSkipPrefixResolver = $this->getService(\RectorPrefix20210511\Symplify\EasyTesting\MissplacedSkipPrefixResolver::class);
        $this->fixtureFinder = $this->getService(\RectorPrefix20210511\Symplify\EasyTesting\Finder\FixtureFinder::class);
    }
    public function test() : void
    {
        $fileInfos = $this->fixtureFinder->find([__DIR__ . '/Fixture']);
        $invalidFileInfos = $this->missplacedSkipPrefixResolver->resolve($fileInfos);
        $this->assertCount(2, $invalidFileInfos);
    }
}
