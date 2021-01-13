<?php declare(strict_types=1);

namespace Rector\Composer\Tests\ValueObject\ComposerModifier;

use PHPUnit\Framework\TestCase;
use Rector\Composer\ValueObject\ComposerModifier\MovePackageToRequire;
use Symplify\ComposerJsonManipulator\ValueObject\ComposerJson;

final class MovePackageToRequireTest extends TestCase
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

        $movePackageToRequire = new MovePackageToRequire('vendor1/package3');
        $this->assertSame($changedComposerJson, $movePackageToRequire->modify($composerJson));
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
        $this->assertSame($changedComposerJson, $movePackageToRequire->modify($composerJson));
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
        $this->assertSame($changedComposerJson, $movePackageToRequire->modify($composerJson));
    }
}
