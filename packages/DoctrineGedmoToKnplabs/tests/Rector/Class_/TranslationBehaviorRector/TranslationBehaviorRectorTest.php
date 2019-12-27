<?php

declare(strict_types=1);

namespace Rector\DoctrineGedmoToKnplabs\Tests\Rector\Class_\TranslationBehaviorRector;

use Nette\Utils\FileSystem;
use Rector\DoctrineGedmoToKnplabs\Rector\Class_\TranslationBehaviorRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class TranslationBehaviorRectorTest extends AbstractRectorTestCase
{
    protected function tearDown(): void
    {
        // remove generated file
        FileSystem::delete(__DIR__ . '/Fixture/SomeClassTranslation.php');
    }

    public function test(): void
    {
        $this->doTestFile(__DIR__ . '/Fixture/fixture.php.inc');

        $generatedFile = sys_get_temp_dir() . '/rector_temp_tests/SomeClassTranslation.php';
        $this->assertFileExists($generatedFile);

        $this->assertFileEquals(__DIR__ . '/Source/ExpectedSomeClassTranslation.php', $generatedFile);
    }

    protected function getRectorClass(): string
    {
        return TranslationBehaviorRector::class;
    }
}
