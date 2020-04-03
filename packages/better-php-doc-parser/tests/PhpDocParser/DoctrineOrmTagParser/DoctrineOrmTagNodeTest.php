<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Tests\PhpDocParser\DoctrineOrmTagParser;

use Iterator;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Class_\EntityTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Class_\TableTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Property_\ColumnTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Property_\JoinTableTagValueNode;
use Rector\BetterPhpDocParser\Tests\PhpDocParser\AbstractPhpDocInfoTest;
use Rector\Core\Testing\StaticFixtureProvider;

final class DoctrineOrmTagNodeTest extends AbstractPhpDocInfoTest
{
    /**
     * @param class-string $nodeType
     * @param class-string $expectedTagValueNodeType
     *
     * @dataProvider provideData()
     */
    public function test(string $directoryPath, string $nodeType, string $expectedTagValueNodeType): void
    {
        $filePaths = StaticFixtureProvider::yieldFileFromDirectory($directoryPath, '*.php');
        foreach ($filePaths as $filePath) {
            $this->doTestPrintedPhpDocInfo($filePath, $nodeType, $expectedTagValueNodeType);
        }
    }

    public function provideData(): Iterator
    {
        yield [__DIR__ . '/Fixture/Property/Column', Property::class, ColumnTagValueNode::class];
        yield [__DIR__ . '/Fixture/Property/JoinTable', Property::class, JoinTableTagValueNode::class];

        yield [__DIR__ . '/Fixture/Class_/Entity', Class_::class, EntityTagValueNode::class];
        yield [__DIR__ . '/Fixture/Class_/Table', Class_::class, TableTagValueNode::class];
    }
}
