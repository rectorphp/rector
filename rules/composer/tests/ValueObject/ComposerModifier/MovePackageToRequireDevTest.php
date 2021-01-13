<?php declare(strict_types=1);

namespace Rector\Composer\Tests\ValueObject\ComposerModifier;

use PHPUnit\Framework\TestCase;
use Rector\Composer\ValueObject\ComposerModifier\MovePackageToRequireDev;
use Symplify\ComposerJsonManipulator\ValueObject\ComposerJson;

final class MovePackageToRequireDevTest extends TestCase
{
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
        $this->assertSame($changedComposerJson, $movePackageToRequireDev->modify($composerJson));
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
        $this->assertSame($changedComposerJson, $movePackageToRequireDev->modify($composerJson));
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
        $this->assertSame($changedComposerJson, $movePackageToRequireDev->modify($composerJson));
    }
}
