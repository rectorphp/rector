<?php

namespace Rector\Composer\Tests\Modifier;

use Rector\Composer\Modifier\RemovePackageModifier;
use Rector\Composer\ValueObject\ComposerModifier\RemovePackage;
use Rector\Core\HttpKernel\RectorKernel;
use Symplify\ComposerJsonManipulator\ValueObject\ComposerJson;
use Symplify\PackageBuilder\Testing\AbstractKernelTestCase;

final class RemovePackageTest extends AbstractKernelTestCase
{
    /** @var RemovePackageModifier */
    private $removePackageModifier;

    protected function setUp(): void
    {
        $this->bootKernelWithConfigs(RectorKernel::class, []);
        $this->removePackageModifier = $this->getService(RemovePackageModifier::class);
    }

    public function testRemoveNonExistingPackage(): void
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

        $removePackage = new RemovePackage('vendor1/package3');
        $this->assertEquals($changedComposerJson, $this->removePackageModifier->modify($composerJson, $removePackage));
    }

    public function testRemoveExistingPackage(): void
    {
        $composerJson = new ComposerJson();
        $composerJson->setRequire([
            'vendor1/package1' => '^1.0',
            'vendor1/package2' => '^2.0',
        ]);

        $changedComposerJson = new ComposerJson();
        $changedComposerJson->setRequire([
            'vendor1/package2' => '^2.0',
        ]);

        $removePackage = new RemovePackage('vendor1/package1');
        $this->assertEquals($changedComposerJson, $this->removePackageModifier->modify($composerJson, $removePackage));
    }

    public function testRemoveExistingDevPackage(): void
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

        $removePackage = new RemovePackage('vendor1/package2');
        $this->assertEquals($changedComposerJson, $this->removePackageModifier->modify($composerJson, $removePackage));
    }
}
