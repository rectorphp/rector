<?php

namespace Rector\Composer\Tests\ValueObject;

use PHPUnit\Framework\TestCase;
use Rector\Composer\ValueObject\MovePackageToRequire;

final class MovePackageToRequireTest extends TestCase
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

        $movePackageToRequire = new MovePackageToRequire('vendor1/package3');
        $this->assertEquals($changedComposerData, $movePackageToRequire->modify($composerData));
    }

    public function testMoveExistingDevPackage(): void
    {
        $composerData = [
            'require-dev' => [
                'vendor1/package1' => '^1.0',
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

        $movePackageToRequire = new MovePackageToRequire('vendor1/package1');
        $this->assertEquals($changedComposerData, $movePackageToRequire->modify($composerData));
    }

    public function testMoveExistingPackage(): void
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

        $movePackageToRequire = new MovePackageToRequire('vendor1/package1');
        $this->assertEquals($changedComposerData, $movePackageToRequire->modify($composerData));
    }
}
