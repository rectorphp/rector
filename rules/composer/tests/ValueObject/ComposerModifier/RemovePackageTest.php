<?php declare(strict_types=1);

namespace Rector\Composer\Tests\ValueObject\ComposerModifier;

use PHPUnit\Framework\TestCase;
use Rector\Composer\ValueObject\ComposerModifier\RemovePackage;
use Symplify\ComposerJsonManipulator\ValueObject\ComposerJson;

final class RemovePackageTest extends TestCase
{
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
        $this->assertSame($changedComposerJson, $removePackage->modify($composerJson));
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
        $this->assertSame($changedComposerJson, $removePackage->modify($composerJson));
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
        $this->assertSame($changedComposerJson, $removePackage->modify($composerJson));
    }
}
