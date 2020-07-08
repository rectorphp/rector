<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Configuration;

use Rector\Core\Configuration\MinimalVersionChecker;
use Rector\Core\Exception\Application\PhpVersionException;
use Symplify\PackageBuilder\Tests\AbstractKernelTestCase;

final class MinimalVersionCheckerTest extends AbstractKernelTestCase
{
    /**
     * @dataProvider dataProvider
     */
    public function test(string $version, bool $shouldThrowException): void
    {
        $composerJsonReader = new MinimalVersionChecker\ComposerJsonReader(
            __DIR__ . '/MinimalVersionChecker/composer-7.2.0.json'
        );
        $minimalVersionChecker = new MinimalVersionChecker(
            $version,
            new MinimalVersionChecker\ComposerJsonParser($composerJsonReader->read())
        );

        if ($shouldThrowException) {
            $this->expectException(PhpVersionException::class);
        } else {
            $this->expectNotToPerformAssertions();
        }

        $minimalVersionChecker->check();
    }

    public function dataProvider()
    {
        return [['7.5.0', false], ['7.5.0-13ubuntu3.2', false], ['7.1.0', true], ['7.1.0-13ubuntu3.2', true]];
    }
}
