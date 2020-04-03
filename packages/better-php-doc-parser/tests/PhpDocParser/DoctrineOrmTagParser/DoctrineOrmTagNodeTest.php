<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Tests\PhpDocParser\DoctrineOrmTagParser;

use Iterator;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use Rector\BetterPhpDocParser\Tests\PhpDocParser\AbstractPhpDocInfoTest;

final class DoctrineOrmTagNodeTest extends AbstractPhpDocInfoTest
{
    /**
     * @dataProvider provideData()
     * @param class-string $nodeType
     */
    public function test(string $filePath, string $nodeType): void
    {
        $this->doTestPrintedPhpDocInfo($filePath, $nodeType);
    }

    public function provideData(): Iterator
    {
        yield [__DIR__ . '/Fixture/SomeEntity.php', Class_::class];
        yield [__DIR__ . '/Fixture/SomeEntitySimple.php', Class_::class];
        yield [__DIR__ . '/Fixture/SkipNonDoctrineEntity.php', ClassMethod::class];
        yield [__DIR__ . '/Fixture/TableWithIndexes.php', Class_::class];
        yield [__DIR__ . '/Fixture/FormattingDoctrineEntity.php', Class_::class];

        yield [__DIR__ . '/Fixture/SomeProperty.php', Property::class];
        yield [__DIR__ . '/Fixture/PropertyWithName.php', Property::class];
        yield [__DIR__ . '/Fixture/FromOfficialDocs.php', Property::class];
        yield [__DIR__ . '/Fixture/JoinTable.php', Property::class];
    }
}
