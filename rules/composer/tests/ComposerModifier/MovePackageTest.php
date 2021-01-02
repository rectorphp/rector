<?php

namespace Rector\Composer\Tests\ComposerModifier;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Rector\Composer\ComposerModifier\MovePackage;
use Rector\Composer\Rector\ComposerRector;

final class MovePackageTest extends TestCase
{
    public function testMoveNonExistingPackage(): void
    {
        $composerData = [
            'require' => [
                'vendor1/package1' => '^1.0',
                'vendor1/package2' => '^2.0',
            ],
        ];
        $changedComposerData = $composerData;

        $movePackage = new MovePackage('vendor1/package3');
        $this->assertEquals($changedComposerData, $movePackage->modify($composerData));
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

        $movePackage = new MovePackage('vendor1/package1');
        $this->assertEquals($changedComposerData, $movePackage->modify($composerData));
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
                'vendor1/package2' => '^2.0',
            ],
        ];

        $movePackage = new MovePackage('vendor1/package2', ComposerRector::SECTION_REQUIRE);
        $this->assertEquals($changedComposerData, $movePackage->modify($composerData));
    }

    public function testMovePackageToUnknownSection(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected one of: "require", "require-dev". Got: "require_dev"');
        new MovePackage('vendor1/package1', 'require_dev');
    }
}
