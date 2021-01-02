<?php

namespace Rector\Composer\Tests\Rector;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Rector\Composer\ComposerModifier\AddPackage;
use Rector\Composer\ComposerModifier\ChangePackage;
use Rector\Composer\ComposerModifier\ChangePackageVersion;
use Rector\Composer\ComposerModifier\MovePackage;
use Rector\Composer\ComposerModifier\RemovePackage;
use Rector\Composer\Rector\ComposerRector;

final class ComposerRectorTest extends TestCase
{
    public function testRefactorWithOneAddedPackage(): void
    {
        $composerRector = new ComposerRector();
        $composerRector->configure([
            new AddPackage('vendor1/package3', '^3.0'),
        ]);

        $originalContent = json_encode([
            'require' => [
                'vendor1/package1' => '^1.0',
                'vendor1/package2' => '^2.0',
            ],
        ]);

        $newContent = json_encode([
            'require' => [
                'vendor1/package1' => '^1.0',
                'vendor1/package2' => '^2.0',
                'vendor1/package3' => '^3.0',
            ],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $this->assertEquals($newContent, $composerRector->refactor($originalContent));
    }

    public function testRefactorWithOneAddedAndOneRemovedPackage(): void
    {
        $composerRector = new ComposerRector();
        $composerRector->configure([
            new AddPackage('vendor1/package3', '^3.0'),
            new RemovePackage('vendor1/package1'),
        ]);

        $originalContent = json_encode([
            'require' => [
                'vendor1/package1' => '^1.0',
                'vendor1/package2' => '^2.0',
            ],
        ]);

        $newContent = json_encode([
            'require' => [
                'vendor1/package2' => '^2.0',
                'vendor1/package3' => '^3.0',
            ],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $this->assertEquals($newContent, $composerRector->refactor($originalContent));
    }

    public function testRefactorWithAddedAndRemovedSamePackage(): void
    {
        $composerRector = new ComposerRector();
        $composerRector->configure([
            new AddPackage('vendor1/package3', '^3.0'),
            new RemovePackage('vendor1/package3'),
        ]);

        $originalContent = json_encode([
            'require' => [
                'vendor1/package1' => '^1.0',
                'vendor1/package2' => '^2.0',
            ],
        ]);

        $newContent = json_encode([
            'require' => [
                'vendor1/package1' => '^1.0',
                'vendor1/package2' => '^2.0',
            ],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $this->assertEquals($newContent, $composerRector->refactor($originalContent));
    }

    public function testRefactorWithRemovedAndAddedBackSamePackage(): void
    {
        $composerRector = new ComposerRector();
        $composerRector->configure([
            new RemovePackage('vendor1/package3'),
            new AddPackage('vendor1/package3', '^3.0'),
        ]);

        $originalContent = json_encode([
            'require' => [
                'vendor1/package1' => '^1.0',
                'vendor1/package2' => '^2.0',
            ],
        ]);

        $newContent = json_encode([
            'require' => [
                'vendor1/package1' => '^1.0',
                'vendor1/package2' => '^2.0',
                'vendor1/package3' => '^3.0',
            ],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $this->assertEquals($newContent, $composerRector->refactor($originalContent));
    }

    public function testRefactorWithMovedAndChangedPackages(): void
    {
        $composerRector = new ComposerRector();
        $composerRector->configure([
            new MovePackage('vendor1/package1'),
            new ChangePackage('vendor1/package2', 'vendor2/package1', '^3.0'),
            new ChangePackageVersion('vendor1/package3', '~3.0.0'),
        ]);

        $originalContent = json_encode([
            'require' => [
                'vendor1/package1' => '^1.0',
                'vendor1/package2' => '^2.0',
                'vendor1/package3' => '^3.0',
            ],
        ]);

        $newContent = json_encode([
            'require' => [
                'vendor1/package3' => '~3.0.0',
                'vendor2/package1' => '^3.0',
            ],
            'require-dev' => [
                'vendor1/package1' => '^1.0',
            ],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $this->assertEquals($newContent, $composerRector->refactor($originalContent));
    }

    public function testRefactorWithMultipleConfiguration(): void
    {
        $composerRector = new ComposerRector();
        $composerRector->configure([
            new MovePackage('vendor1/package1'),
        ]);
        $composerRector->configure([
            new ChangePackage('vendor1/package2', 'vendor2/package1', '^3.0'),
        ]);
        $composerRector->configure([
            new ChangePackageVersion('vendor1/package3', '~3.0.0'),
        ]);

        $originalContent = json_encode([
            'require' => [
                'vendor1/package1' => '^1.0',
                'vendor1/package2' => '^2.0',
                'vendor1/package3' => '^3.0',
            ],
        ]);

        $newContent = json_encode([
            'require' => [
                'vendor1/package3' => '~3.0.0',
                'vendor2/package1' => '^3.0',
            ],
            'require-dev' => [
                'vendor1/package1' => '^1.0',
            ],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $this->assertEquals($newContent, $composerRector->refactor($originalContent));
    }

    public function testRefactorWithConfigurationAndReconfigurationAndConfiguration(): void
    {
        $composerRector = new ComposerRector();
        $composerRector->configure([
            new MovePackage('vendor1/package1'),
        ]);
        $composerRector->reconfigure([
            new ChangePackage('vendor1/package2', 'vendor2/package1', '^3.0'),
        ]);
        $composerRector->configure([
            new ChangePackageVersion('vendor1/package3', '~3.0.0'),
        ]);

        $originalContent = json_encode([
            'require' => [
                'vendor1/package1' => '^1.0',
                'vendor1/package2' => '^2.0',
                'vendor1/package3' => '^3.0',
            ],
        ]);

        $newContent = json_encode([
            'require' => [
                'vendor1/package1' => '^1.0',
                'vendor1/package3' => '~3.0.0',
                'vendor2/package1' => '^3.0',
            ],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $this->assertEquals($newContent, $composerRector->refactor($originalContent));
    }

    public function testFilePath(): void
    {
        $composerRector = new ComposerRector();
        $this->assertEquals(getcwd() . '/composer.json', $composerRector->getFilePath());

        $composerRector->setFilePath('test/composer.json');
        $this->assertEquals('test/composer.json', $composerRector->getFilePath());
    }

    public function testCommand(): void
    {
        $composerRector = new ComposerRector();
        $this->assertEquals('composer update', $composerRector->getCommand());

        $composerRector->setCommand('composer update --prefer-stable');
        $this->assertEquals('composer update --prefer-stable', $composerRector->getCommand());
    }

    public function testWrongConfigureString(): void
    {
        $composerRector = new ComposerRector();

        $this->expectException(InvalidArgumentException::class);
        $composerRector->configure([
            'a',
        ]);
    }

    public function testWrongConfigureInt(): void
    {
        $composerRector = new ComposerRector();

        $this->expectException(InvalidArgumentException::class);
        $composerRector->configure([
            1,
        ]);
    }

    public function testWrongConfigureClass(): void
    {
        $composerRector = new ComposerRector();

        $this->expectException(InvalidArgumentException::class);
        $composerRector->configure([
            new ComposerRector(),
        ]);
    }

    public function testWrongReconfigureString(): void
    {
        $composerRector = new ComposerRector();

        $this->expectException(InvalidArgumentException::class);
        $composerRector->reconfigure([
            'a',
        ]);
    }

    public function testWrongReconfigureInt(): void
    {
        $composerRector = new ComposerRector();

        $this->expectException(InvalidArgumentException::class);
        $composerRector->reconfigure([
            1,
        ]);
    }

    public function testWrongReconfigureClass(): void
    {
        $composerRector = new ComposerRector();

        $this->expectException(InvalidArgumentException::class);
        $composerRector->reconfigure([
            new ComposerRector(),
        ]);
    }
}
