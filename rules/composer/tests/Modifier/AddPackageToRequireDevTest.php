<?php

namespace Rector\Composer\Tests\Modifier;

use Rector\Composer\Modifier\AddPackageToRequireDevModifier;
use Rector\Composer\ValueObject\ComposerModifier\AddPackageToRequireDev;
use Rector\Core\HttpKernel\RectorKernel;
use Symplify\ComposerJsonManipulator\ValueObject\ComposerJson;
use Symplify\PackageBuilder\Testing\AbstractKernelTestCase;

final class AddPackageToRequireDevTest extends AbstractKernelTestCase
{
    /** @var AddPackageToRequireDevModifier */
    private $addPackageToRequireDevModifier;

    protected function setUp(): void
    {
        $this->bootKernelWithConfigs(RectorKernel::class, []);
        $this->addPackageToRequireDevModifier = $this->getService(AddPackageToRequireDevModifier::class);
    }

    public function testAddNonExistingPackage(): void
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
        $changedComposerJson->setRequireDev([
            'vendor1/package3' => '^3.0',
        ]);

        $addPackageToRequireDev = new AddPackageToRequireDev('vendor1/package3', '^3.0');
        $this->assertEquals($changedComposerJson, $this->addPackageToRequireDevModifier->modify($composerJson, $addPackageToRequireDev));
    }

    public function testAddExistingPackage(): void
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

        $addPackageToRequireDev = new AddPackageToRequireDev('vendor1/package1', '^3.0');
        $this->assertEquals($changedComposerJson, $this->addPackageToRequireDevModifier->modify($composerJson, $addPackageToRequireDev));
    }

    public function testAddExistingDevPackage(): void
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
            'vendor1/package2' => '^2.0',
        ]);

        $addPackageToRequireDev = new AddPackageToRequireDev('vendor1/package2', '^3.0');
        $this->assertEquals($changedComposerJson, $this->addPackageToRequireDevModifier->modify($composerJson, $addPackageToRequireDev));
    }
}
