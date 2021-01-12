<?php

namespace Rector\Composer\Tests\Modifier;

use Rector\Composer\Modifier\AddPackageToRequireModifier;
use Rector\Composer\ValueObject\ComposerModifier\AddPackageToRequire;
use Rector\Core\HttpKernel\RectorKernel;
use Symplify\ComposerJsonManipulator\ValueObject\ComposerJson;
use Symplify\PackageBuilder\Testing\AbstractKernelTestCase;

final class AddPackageToRequireTest extends AbstractKernelTestCase
{
    /** @var AddPackageToRequireModifier */
    private $addPackageToRequireModifier;

    protected function setUp(): void
    {
        $this->bootKernelWithConfigs(RectorKernel::class, []);
        $this->addPackageToRequireModifier = $this->getService(AddPackageToRequireModifier::class);
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
            'vendor1/package3' => '^3.0',
        ]);

        $addPackageToRequire = new AddPackageToRequire('vendor1/package3', '^3.0');
        $this->assertEquals($changedComposerJson, $this->addPackageToRequireModifier->modify($composerJson, $addPackageToRequire));
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

        $addPackageToRequire = new AddPackageToRequire('vendor1/package1', '^3.0');
        $this->assertEquals($changedComposerJson, $this->addPackageToRequireModifier->modify($composerJson, $addPackageToRequire));
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

        $addPackageToRequire = new AddPackageToRequire('vendor1/package2', '^3.0');
        $this->assertEquals($changedComposerJson, $this->addPackageToRequireModifier->modify($composerJson, $addPackageToRequire));
    }
}
