<?php declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Tests\PhpDocParser\OrmTagParser\Class_;

use Iterator;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\BetterPhpDocParser\Tests\PhpDocParser\OrmTagParser\AbstractPhpDocInfoTest;

final class DoctrinePhpDocParserTest extends AbstractPhpDocInfoTest
{
    /**
     * @dataProvider provideData()
     */
    public function test(string $filePath, string $expectedPrintedPhpDoc, string $type): void
    {
        $class = $this->parseFileAndGetFirstNodeOfType($filePath, $type);
        $printedPhpDocInfo = $this->createPhpDocInfoFromNodeAndPrintBackToString($class);

        $this->assertStringEqualsFile($expectedPrintedPhpDoc, $printedPhpDocInfo);
    }

    public function provideData(): Iterator
    {
        yield [__DIR__ . '/Fixture/SomeEntity.php', __DIR__ . '/Fixture/expected_some_entity.txt', Class_::class];
        yield [
            __DIR__ . '/Fixture/SkipNonDoctrineEntity.php',
            __DIR__ . '/Fixture/expected_skip_non_doctrine_entity.txt',
            ClassMethod::class,
        ];
    }
}
