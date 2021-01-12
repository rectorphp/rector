<?php

namespace Rector\Composer\Tests\ValueObject\ComposerModifier;

use PHPUnit\Framework\TestCase;
use Rector\Composer\ValueObject\ComposerModifier\AddPackageToRequire;
use Symplify\ComposerJsonManipulator\ValueObject\ComposerJson;

final class AddPackageToRequireTest extends TestCase
{
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
        $this->assertEquals($changedComposerJson, $addPackageToRequire->modify($composerJson));
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
        $this->assertEquals($changedComposerJson, $addPackageToRequire->modify($composerJson));
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
        $this->assertEquals($changedComposerJson, $addPackageToRequire->modify($composerJson));
    }
}
