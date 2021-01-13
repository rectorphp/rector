<?php

declare(strict_types=1);

namespace Rector\Composer\Tests\ValueObject\ComposerModifier;

use PHPUnit\Framework\TestCase;
use Rector\Composer\ValueObject\ComposerModifier\ChangePackageVersion;
use Symplify\ComposerJsonManipulator\ValueObject\ComposerJson;

final class ChangePackageVersionTest extends TestCase
{
    public function testChangeVersionNonExistingPackage(): void
    {
        $composerJson = new ComposerJson();
        $composerJson->setRequire([
            'vendor1/package1' => '^1.0',
            'vendor1/package2' => '^2.0',
        ]);

        $expectedComposerJson = new ComposerJson();
        $expectedComposerJson->setRequire([
            'vendor1/package1' => '^1.0',
            'vendor1/package2' => '^2.0',
        ]);

        $changePackageVersion = new ChangePackageVersion('vendor1/package3', '^3.0');
        $changePackageVersion->modify($composerJson);
        $this->assertSame($expectedComposerJson->getJsonArray(), $composerJson->getJsonArray());
    }

    public function testChangeVersionExistingPackage(): void
    {
        $composerJson = new ComposerJson();
        $composerJson->setRequire([
            'vendor1/package1' => '^1.0',
            'vendor1/package2' => '^2.0',
        ]);

        $expectedComposerJson = new ComposerJson();
        $expectedComposerJson->setRequire([
            'vendor1/package1' => '^3.0',
            'vendor1/package2' => '^2.0',
        ]);

        $changePackageVersion = new ChangePackageVersion('vendor1/package1', '^3.0');
        $changePackageVersion->modify($composerJson);
        $this->assertSame($expectedComposerJson->getJsonArray(), $composerJson->getJsonArray());
    }

    public function testChangeVersionExistingDevPackage(): void
    {
        $composerJson = new ComposerJson();
        $composerJson->setRequire([
            'vendor1/package1' => '^1.0',
        ]);
        $composerJson->setRequireDev([
            'vendor1/package2' => '^2.0',
        ]);

        $expectedComposerJson = new ComposerJson();
        $expectedComposerJson->setRequire([
            'vendor1/package1' => '^1.0',
        ]);
        $expectedComposerJson->setRequireDev([
            'vendor1/package2' => '^3.0',
        ]);

        $changePackageVersion = new ChangePackageVersion('vendor1/package2', '^3.0');
        $changePackageVersion->modify($composerJson);

        $this->assertSame($expectedComposerJson->getJsonArray(), $composerJson->getJsonArray());
    }
}
