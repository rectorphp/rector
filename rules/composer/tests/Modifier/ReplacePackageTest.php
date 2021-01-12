<?php

namespace Rector\Composer\Tests\Modifier;

use Rector\Composer\Modifier\ReplacePackageModifier;
use Rector\Composer\ValueObject\ComposerModifier\ReplacePackage;
use Rector\Core\HttpKernel\RectorKernel;
use Symplify\ComposerJsonManipulator\ValueObject\ComposerJson;
use Symplify\PackageBuilder\Testing\AbstractKernelTestCase;

final class ReplacePackageTest extends AbstractKernelTestCase
{
    /** @var ReplacePackageModifier */
    private $replacePackageModifier;

    protected function setUp(): void
    {
        $this->bootKernelWithConfigs(RectorKernel::class, []);
        $this->replacePackageModifier = $this->getService(ReplacePackageModifier::class);
    }

    public function testReplaceNonExistingPackage(): void
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

        $replacePackage = new ReplacePackage('vendor1/package3', 'vendor1/package4', '^3.0');
        $this->assertEquals($changedComposerJson, $this->replacePackageModifier->modify($composerJson, $replacePackage));
    }

    public function testReplaceExistingPackage(): void
    {
        $composerJson = new ComposerJson();
        $composerJson->setRequire([
            'vendor1/package1' => '^1.0',
            'vendor1/package2' => '^2.0',
        ]);

        $changedComposerJson = new ComposerJson();
        $changedComposerJson->setRequire([
            'vendor1/package3' => '^3.0',
            'vendor1/package2' => '^2.0',
        ]);

        $replacePackage = new ReplacePackage('vendor1/package1', 'vendor1/package3', '^3.0');
        $this->assertEquals($changedComposerJson, $this->replacePackageModifier->modify($composerJson, $replacePackage));
    }

    public function testReplaceExistingDevPackage(): void
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
                'vendor1/package3' => '^3.0',
        ]);

        $replacePackage = new ReplacePackage('vendor1/package2', 'vendor1/package3', '^3.0');
        $this->assertEquals($changedComposerJson, $this->replacePackageModifier->modify($composerJson, $replacePackage));
    }
}
