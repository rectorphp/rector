<?php

namespace Rector\Composer\Tests\Modifier;

use Rector\Composer\Modifier\MovePackageToRequireModifier;
use Rector\Composer\ValueObject\ComposerModifier\MovePackageToRequire;
use Rector\Core\HttpKernel\RectorKernel;
use Symplify\ComposerJsonManipulator\ValueObject\ComposerJson;
use Symplify\PackageBuilder\Testing\AbstractKernelTestCase;

final class MovePackageToRequireTest extends AbstractKernelTestCase
{
    /** @var MovePackageToRequireModifier */
    private $movePackageToRequireModifier;

    protected function setUp(): void
    {
        $this->bootKernelWithConfigs(RectorKernel::class, []);
        $this->movePackageToRequireModifier = $this->getService(MovePackageToRequireModifier::class);
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

        $movePackageToRequire = new MovePackageToRequire('vendor1/package3');
        $this->assertEquals($changedComposerJson, $this->movePackageToRequireModifier->modify($composerJson, $movePackageToRequire));
    }

    public function testMoveExistingDevPackage(): void
    {
        $composerJson = new ComposerJson();
        $composerJson->setRequireDev([
            'vendor1/package1' => '^1.0',
            'vendor1/package2' => '^2.0',
        ]);

        $changedComposerJson = new ComposerJson();
        $changedComposerJson->setRequire([
            'vendor1/package1' => '^1.0',
        ]);
        $changedComposerJson->setRequireDev([
            'vendor1/package2' => '^2.0',
        ]);

        $movePackageToRequire = new MovePackageToRequire('vendor1/package1');
        $this->assertEquals($changedComposerJson, $this->movePackageToRequireModifier->modify($composerJson, $movePackageToRequire));
    }

    public function testMoveExistingPackage(): void
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

        $movePackageToRequire = new MovePackageToRequire('vendor1/package1');
        $this->assertEquals($changedComposerJson, $this->movePackageToRequireModifier->modify($composerJson, $movePackageToRequire));
    }
}
