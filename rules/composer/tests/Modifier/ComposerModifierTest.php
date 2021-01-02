<?php

namespace Rector\Composer\Tests\Modifier;

use Nette\Utils\Json;
use PHPUnit\Framework\TestCase;
use Rector\Composer\ValueObject\AddPackage;
use Rector\Composer\ValueObject\ChangePackage;
use Rector\Composer\ValueObject\ChangePackageVersion;
use Rector\Composer\ValueObject\MovePackage;
use Rector\Composer\ValueObject\RemovePackage;
use Rector\Composer\Modifier\ComposerModifier;

final class ComposerModifierTest extends TestCase
{
    public function testRefactorWithOneAddedPackage(): void
    {
        $composerRector = new ComposerModifier();
        $composerRector->configure([
            new AddPackage('vendor1/package3', '^3.0'),
        ]);

        $originalContent = Json::encode([
            'require' => [
                'vendor1/package1' => '^1.0',
                'vendor1/package2' => '^2.0',
            ],
        ]);

        $newContent = Json::encode([
            'require' => [
                'vendor1/package1' => '^1.0',
                'vendor1/package2' => '^2.0',
                'vendor1/package3' => '^3.0',
            ],
        ], Json::PRETTY);
        $this->assertEquals($newContent, $composerRector->modify($originalContent));
    }

    public function testRefactorWithOneAddedAndOneRemovedPackage(): void
    {
        $composerRector = new ComposerModifier();
        $composerRector->configure([
            new AddPackage('vendor1/package3', '^3.0'),
            new RemovePackage('vendor1/package1'),
        ]);

        $originalContent = Json::encode([
            'require' => [
                'vendor1/package1' => '^1.0',
                'vendor1/package2' => '^2.0',
            ],
        ]);

        $newContent = Json::encode([
            'require' => [
                'vendor1/package2' => '^2.0',
                'vendor1/package3' => '^3.0',
            ],
        ], Json::PRETTY);
        $this->assertEquals($newContent, $composerRector->modify($originalContent));
    }

    public function testRefactorWithAddedAndRemovedSamePackage(): void
    {
        $composerRector = new ComposerModifier();
        $composerRector->configure([
            new AddPackage('vendor1/package3', '^3.0'),
            new RemovePackage('vendor1/package3'),
        ]);

        $originalContent = Json::encode([
            'require' => [
                'vendor1/package1' => '^1.0',
                'vendor1/package2' => '^2.0',
            ],
        ]);

        $newContent = Json::encode([
            'require' => [
                'vendor1/package1' => '^1.0',
                'vendor1/package2' => '^2.0',
            ],
        ], Json::PRETTY);
        $this->assertEquals($newContent, $composerRector->modify($originalContent));
    }

    public function testRefactorWithRemovedAndAddedBackSamePackage(): void
    {
        $composerRector = new ComposerModifier();
        $composerRector->configure([
            new RemovePackage('vendor1/package3'),
            new AddPackage('vendor1/package3', '^3.0'),
        ]);

        $originalContent = Json::encode([
            'require' => [
                'vendor1/package1' => '^1.0',
                'vendor1/package2' => '^2.0',
            ],
        ]);

        $newContent = Json::encode([
            'require' => [
                'vendor1/package1' => '^1.0',
                'vendor1/package2' => '^2.0',
                'vendor1/package3' => '^3.0',
            ],
        ], Json::PRETTY);
        $this->assertEquals($newContent, $composerRector->modify($originalContent));
    }

    public function testRefactorWithMovedAndChangedPackages(): void
    {
        $composerRector = new ComposerModifier();
        $composerRector->configure([
            new MovePackage('vendor1/package1'),
            new ChangePackage('vendor1/package2', 'vendor2/package1', '^3.0'),
            new ChangePackageVersion('vendor1/package3', '~3.0.0'),
        ]);

        $originalContent = Json::encode([
            'require' => [
                'vendor1/package1' => '^1.0',
                'vendor1/package2' => '^2.0',
                'vendor1/package3' => '^3.0',
            ],
        ]);

        $newContent = Json::encode([
            'require' => [
                'vendor1/package3' => '~3.0.0',
                'vendor2/package1' => '^3.0',
            ],
            'require-dev' => [
                'vendor1/package1' => '^1.0',
            ],
        ], Json::PRETTY);
        $this->assertEquals($newContent, $composerRector->modify($originalContent));
    }

    public function testRefactorWithMultipleConfiguration(): void
    {
        $composerRector = new ComposerModifier();
        $composerRector->configure([
            new MovePackage('vendor1/package1'),
        ]);
        $composerRector->configure([
            new ChangePackage('vendor1/package2', 'vendor2/package1', '^3.0'),
        ]);
        $composerRector->configure([
            new ChangePackageVersion('vendor1/package3', '~3.0.0'),
        ]);

        $originalContent = Json::encode([
            'require' => [
                'vendor1/package1' => '^1.0',
                'vendor1/package2' => '^2.0',
                'vendor1/package3' => '^3.0',
            ],
        ]);

        $newContent = Json::encode([
            'require' => [
                'vendor1/package3' => '~3.0.0',
                'vendor2/package1' => '^3.0',
            ],
            'require-dev' => [
                'vendor1/package1' => '^1.0',
            ],
        ], Json::PRETTY);
        $this->assertEquals($newContent, $composerRector->modify($originalContent));
    }

    public function testRefactorWithConfigurationAndReconfigurationAndConfiguration(): void
    {
        $composerRector = new ComposerModifier();
        $composerRector->configure([
            new MovePackage('vendor1/package1'),
        ]);
        $composerRector->reconfigure([
            new ChangePackage('vendor1/package2', 'vendor2/package1', '^3.0'),
        ]);
        $composerRector->configure([
            new ChangePackageVersion('vendor1/package3', '~3.0.0'),
        ]);

        $originalContent = Json::encode([
            'require' => [
                'vendor1/package1' => '^1.0',
                'vendor1/package2' => '^2.0',
                'vendor1/package3' => '^3.0',
            ],
        ]);

        $newContent = Json::encode([
            'require' => [
                'vendor1/package1' => '^1.0',
                'vendor1/package3' => '~3.0.0',
                'vendor2/package1' => '^3.0',
            ],
        ], Json::PRETTY);
        $this->assertEquals($newContent, $composerRector->modify($originalContent));
    }

    public function testFilePath(): void
    {
        $composerRector = new ComposerModifier();
        $this->assertEquals(getcwd() . '/composer.json', $composerRector->getFilePath());

        $composerRector->filePath('test/composer.json');
        $this->assertEquals('test/composer.json', $composerRector->getFilePath());
    }

    public function testCommand(): void
    {
        $composerRector = new ComposerModifier();
        $this->assertEquals('composer update', $composerRector->getCommand());

        $composerRector->command('composer update --prefer-stable');
        $this->assertEquals('composer update --prefer-stable', $composerRector->getCommand());
    }
}
