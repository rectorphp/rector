<?php

declare (strict_types=1);
namespace RectorPrefix20210511\Symplify\Skipper\Tests\SkipCriteriaResolver\SkippedPathsResolver;

use RectorPrefix20210511\Symplify\PackageBuilder\Testing\AbstractKernelTestCase;
use RectorPrefix20210511\Symplify\Skipper\HttpKernel\SkipperKernel;
use RectorPrefix20210511\Symplify\Skipper\SkipCriteriaResolver\SkippedPathsResolver;
final class SkippedPathsResolverTest extends \RectorPrefix20210511\Symplify\PackageBuilder\Testing\AbstractKernelTestCase
{
    /**
     * @var SkippedPathsResolver
     */
    private $skippedPathsResolver;
    protected function setUp() : void
    {
        $this->bootKernelWithConfigs(\RectorPrefix20210511\Symplify\Skipper\HttpKernel\SkipperKernel::class, [__DIR__ . '/config/config.php']);
        $this->skippedPathsResolver = $this->getService(\RectorPrefix20210511\Symplify\Skipper\SkipCriteriaResolver\SkippedPathsResolver::class);
    }
    public function test() : void
    {
        $skippedPaths = $this->skippedPathsResolver->resolve();
        $this->assertCount(2, $skippedPaths);
        $this->assertSame('*/Mask/*', $skippedPaths[1]);
    }
}
