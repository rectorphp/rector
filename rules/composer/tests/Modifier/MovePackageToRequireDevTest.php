<?php

namespace Rector\Composer\Tests\Modifier;

use Rector\Composer\Modifier\MovePackageToRequireDevModifier;
use Rector\Composer\ValueObject\ComposerModifier\MovePackageToRequireDev;
use Rector\Core\HttpKernel\RectorKernel;
use Symplify\ComposerJsonManipulator\ValueObject\ComposerJson;
use Symplify\PackageBuilder\Testing\AbstractKernelTestCase;

final class MovePackageToRequireDevTest extends AbstractKernelTestCase
{
    /** @var MovePackageToRequireDevModifier */
    private $movePackageToRequireDevModifier;

    protected function setUp(): void
    {
        $this->bootKernelWithConfigs(RectorKernel::class, []);
        $this->movePackageToRequireDevModifier = $this->getService(MovePackageToRequireDevModifier::class);
    }

    public function testMoveNonExistingPackage(): void
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

        $movePackageToRequireDev = new MovePackageToRequireDev('vendor1/package3');
        $this->assertEquals($changedComposerJson, $this->movePackageToRequireDevModifier->modify($composerJson, $movePackageToRequireDev));
    }

    public function testMoveExistingPackage(): void
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
        $changedComposerJson->setRequireDev([
            'vendor1/package1' => '^1.0',
        ]);

        $movePackageToRequireDev = new MovePackageToRequireDev('vendor1/package1');
        $this->assertEquals($changedComposerJson, $this->movePackageToRequireDevModifier->modify($composerJson, $movePackageToRequireDev));
    }

    public function testMoveExistingDevPackage(): void
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

        $movePackageToRequireDev = new MovePackageToRequireDev('vendor1/package2');
        $this->assertEquals($changedComposerJson, $this->movePackageToRequireDevModifier->modify($composerJson, $movePackageToRequireDev));
    }
}
