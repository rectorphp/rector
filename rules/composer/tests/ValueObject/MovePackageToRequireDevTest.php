<?php

namespace Rector\Composer\Tests\ValueObject;

use PHPUnit\Framework\TestCase;
use Rector\Composer\ValueObject\MovePackageToRequireDev;

final class MovePackageToRequireDevTest extends TestCase
{
    public function testMoveNonExistingPackage(): void
    {
        $composerData = [
            'require' => [
                'vendor1/package1' => '^1.0',
                'vendor1/package2' => '^2.0',
            ],
        ];

        $changedComposerData = [
            'require' => [
                'vendor1/package1' => '^1.0',
                'vendor1/package2' => '^2.0',
            ],
        ];

        $movePackageToRequireDev = new MovePackageToRequireDev('vendor1/package3');
        $this->assertEquals($changedComposerData, $movePackageToRequireDev->modify($composerData));
    }

    public function testMoveExistingPackage(): void
    {
        $composerData = [
            'require' => [
                'vendor1/package1' => '^1.0',
                'vendor1/package2' => '^2.0',
            ],
        ];

        $changedComposerData = [
            'require' => [
                'vendor1/package2' => '^2.0',
            ],
            'require-dev' => [
                'vendor1/package1' => '^1.0',
            ],
        ];

        $movePackageToRequireDev = new MovePackageToRequireDev('vendor1/package1');
        $this->assertEquals($changedComposerData, $movePackageToRequireDev->modify($composerData));
    }

    public function testMoveExistingDevPackage(): void
    {
        $composerData = [
            'require' => [
                'vendor1/package1' => '^1.0',
            ],
            'require-dev' => [
                'vendor1/package2' => '^2.0',
            ],
        ];

        $changedComposerData = [
            'require' => [
                'vendor1/package1' => '^1.0',
            ],
            'require-dev' => [
                'vendor1/package2' => '^2.0',
            ],
        ];

        $movePackageToRequireDev = new MovePackageToRequireDev('vendor1/package2');
        $this->assertEquals($changedComposerData, $movePackageToRequireDev->modify($composerData));
    }
}
