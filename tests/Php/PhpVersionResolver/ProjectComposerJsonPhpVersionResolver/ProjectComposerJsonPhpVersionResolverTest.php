<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Php\PhpVersionResolver\ProjectComposerJsonPhpVersionResolver;

use Iterator;
use Rector\Core\HttpKernel\RectorKernel;
use Rector\Core\Php\PhpVersionResolver\ProjectComposerJsonPhpVersionResolver;
use Symplify\PackageBuilder\Testing\AbstractKernelTestCase;

final class ProjectComposerJsonPhpVersionResolverTest extends AbstractKernelTestCase
{
    /**
     * @var ProjectComposerJsonPhpVersionResolver
     */
    private $projectComposerJsonPhpVersionResolver;

    protected function setUp(): void
    {
        $this->bootKernel(RectorKernel::class);
        $this->projectComposerJsonPhpVersionResolver = $this->getService(ProjectComposerJsonPhpVersionResolver::class);
    }

    /**
     * @dataProvider provideData()
     */
    public function test(string $composerJsonFilePath, int $expectedPhpVersion): void
    {
        $resolvePhpVersion = $this->projectComposerJsonPhpVersionResolver->resolve($composerJsonFilePath);
        $this->assertSame($expectedPhpVersion, $resolvePhpVersion);
    }

    public function provideData(): Iterator
    {
        yield [__DIR__ . '/Fixture/some_composer.json', 70300];
        yield [__DIR__ . '/Fixture/some_composer_with_platform.json', 70400];
    }
}
