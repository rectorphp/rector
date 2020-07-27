<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Configuration;

use Iterator;
use Rector\Core\Configuration\MinimalVersionChecker;
use Rector\Core\Configuration\MinimalVersionChecker\ComposerJsonParser;
use Rector\Core\Configuration\MinimalVersionChecker\ComposerJsonReader;
use Rector\Core\Exception\Application\PhpVersionException;
use Symplify\PackageBuilder\Tests\AbstractKernelTestCase;

final class MinimalVersionCheckerTest extends AbstractKernelTestCase
{
    /**
     * @dataProvider dataProvider
     */
    public function test(string $version, bool $shouldThrowException): void
    {
        $composerJsonReader = new ComposerJsonReader(__DIR__ . '/MinimalVersionChecker/composer-7.2.0.json');
        $minimalVersionChecker = new MinimalVersionChecker(
            $version,
            new ComposerJsonParser($composerJsonReader->read())
        );

        if ($shouldThrowException) {
            $this->expectException(PhpVersionException::class);
        } else {
            $this->expectNotToPerformAssertions();
        }

        $minimalVersionChecker->check();
    }

    public function dataProvider(): Iterator
    {
        yield ['7.5.0', false];
        yield ['7.5.0-13ubuntu3.2', false];
        yield ['7.1.0', true];
        yield ['7.1.0-13ubuntu3.2', true];
    }
}
