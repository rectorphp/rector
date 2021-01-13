<?php

declare(strict_types=1);

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

        $expectedComposerJson = new ComposerJson();
        $expectedComposerJson->setRequire([
            'vendor1/package1' => '^1.0',
            'vendor1/package2' => '^2.0',
        ]);

        $movePackageToRequireDev = new MovePackageToRequireDev('vendor1/package3');
        $movePackageToRequireDev->modify($composerJson);
        $this->assertSame($expectedComposerJson->getJsonArray(), $composerJson->getJsonArray());
    }

    public function testMoveExistingPackage(): void
    {
        $composerJson = new ComposerJson();
        $composerJson->setRequire([
            'vendor1/package1' => '^1.0',
            'vendor1/package2' => '^2.0',
        ]);

        $expectedComposerJson = new ComposerJson();
        $expectedComposerJson->setRequire([
            'vendor1/package2' => '^2.0',
        ]);
        $expectedComposerJson->setRequireDev([
            'vendor1/package1' => '^1.0',
        ]);

        $movePackageToRequireDev = new MovePackageToRequireDev('vendor1/package1');
        $movePackageToRequireDev->modify($composerJson);
        $this->assertSame($expectedComposerJson->getJsonArray(), $composerJson->getJsonArray());
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

        $expectedComposerJson = new ComposerJson();
        $expectedComposerJson->setRequire([
            'vendor1/package1' => '^1.0',
        ]);
        $expectedComposerJson->setRequireDev([
            'vendor1/package2' => '^2.0',
        ]);

        $movePackageToRequireDev = new MovePackageToRequireDev('vendor1/package2');
        $movePackageToRequireDev->modify($composerJson);
        $this->assertSame($expectedComposerJson->getJsonArray(), $composerJson->getJsonArray());
    }
}
