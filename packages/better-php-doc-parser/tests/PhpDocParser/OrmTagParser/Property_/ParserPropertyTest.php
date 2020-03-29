<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Tests\PhpDocParser\OrmTagParser\Property_;

use Iterator;
use PhpParser\Node\Stmt\Property;
use Rector\BetterPhpDocParser\Tests\PhpDocParser\OrmTagParser\AbstractPhpDocInfoTest;

final class ParserPropertyTest extends AbstractPhpDocInfoTest
{
    /**
     * @dataProvider provideData()
     */
    public function test(string $filePath, string $expectedPrintedPhpDoc): void
    {
        $property = $this->parseFileAndGetFirstNodeOfType($filePath, Property::class);
        $printedPhpDocInfo = $this->printNodePhpDocInfoToString($property);

        $this->assertStringEqualsFile($expectedPrintedPhpDoc, $printedPhpDocInfo);
    }

    public function provideData(): Iterator
    {
        yield [__DIR__ . '/Fixture/SomeProperty.php', __DIR__ . '/Fixture/expected_some_property.txt'];

        yield [__DIR__ . '/Fixture/PropertyWithName.php', __DIR__ . '/Fixture/expected_property_with_name.txt'];

        yield [__DIR__ . '/Fixture/FromOfficialDocs.php', __DIR__ . '/Fixture/expected_from_official_docs.txt'];

        yield [__DIR__ . '/Fixture/JoinTable.php', __DIR__ . '/Fixture/expected_join_table.txt'];
    }
}
