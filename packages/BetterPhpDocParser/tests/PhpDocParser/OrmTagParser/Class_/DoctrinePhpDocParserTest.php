<?php declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Tests\PhpDocParser\OrmTagParser\Class_;

use PhpParser\Node\Stmt\Class_;
use Rector\BetterPhpDocParser\Tests\PhpDocParser\OrmTagParser\AbstractOrmTagParserTest;

final class DoctrinePhpDocParserTest extends AbstractOrmTagParserTest
{
    /**
     * @dataProvider provideData()
     */
    public function test(string $filePath, string $expectedPrintedPhpDoc): void
    {
        $class = $this->parseFileAndGetFirstNodeOfType($filePath, Class_::class);
        $printedPhpDocInfo = $this->createPhpDocInfoFromNodeAndPrintBackToString($class);

        $this->assertStringEqualsFile($expectedPrintedPhpDoc, $printedPhpDocInfo);
    }

    public function provideData(): iterable
    {
        yield [__DIR__ . '/Fixture/SomeEntity.php', __DIR__ . '/Fixture/expected_some_entity.txt'];
    }
}
