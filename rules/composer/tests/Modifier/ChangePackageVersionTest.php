<?php

namespace Rector\Composer\Tests\Modifier;

use Rector\Composer\Modifier\ChangePackageVersionModifier;
use Rector\Composer\ValueObject\ComposerModifier\ChangePackageVersion;
use Rector\Core\HttpKernel\RectorKernel;
use Symplify\ComposerJsonManipulator\ValueObject\ComposerJson;
use Symplify\PackageBuilder\Testing\AbstractKernelTestCase;

final class ChangePackageVersionTest extends AbstractKernelTestCase
{
    /** @var ChangePackageVersionModifier */
    private $changePackageVersionModifier;

    protected function setUp(): void
    {
        $this->bootKernelWithConfigs(RectorKernel::class, []);
        $this->changePackageVersionModifier = $this->getService(ChangePackageVersionModifier::class);
    }

    public function testChangeVersionNonExistingPackage(): void
    {
        $composerJson = new ComposerJson();
        $composerJson->setRequire([
            'vendor1/package1' => '^1.0',
            'vendor1/package2' => '^2.0',
        ]);

        $changedComposerJson = new ComposerJson();
        $changedComposerJson->setRequire([
            'vendor1/package1' => '^1.0',
            'vendor1/package2' => '^2.0',
        ]);

        $changePackageVersion = new ChangePackageVersion('vendor1/package3', '^3.0');
        $this->assertEquals($changedComposerJson, $this->changePackageVersionModifier->modify($composerJson, $changePackageVersion));
    }

    public function testChangeVersionExistingPackage(): void
    {
        $composerJson = new ComposerJson();
        $composerJson->setRequire([
            'vendor1/package1' => '^1.0',
            'vendor1/package2' => '^2.0',
        ]);

        $changedComposerJson = new ComposerJson();
        $changedComposerJson->setRequire([
            'vendor1/package1' => '^3.0',
            'vendor1/package2' => '^2.0',
        ]);

        $changePackageVersion = new ChangePackageVersion('vendor1/package1', '^3.0');
        $this->assertEquals($changedComposerJson, $this->changePackageVersionModifier->modify($composerJson, $changePackageVersion));
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

        $changedComposerJson = new ComposerJson();
        $changedComposerJson->setRequire([
            'vendor1/package1' => '^1.0',
        ]);
        $changedComposerJson->setRequireDev([
            'vendor1/package2' => '^3.0',
        ]);

        $changePackageVersion = new ChangePackageVersion('vendor1/package2', '^3.0');
        $this->assertEquals($changedComposerJson, $this->changePackageVersionModifier->modify($composerJson, $changePackageVersion));
    }
}
