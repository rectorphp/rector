<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Tests\PhpDocParser\DoctrineOrmTagParser;

use Iterator;
use Nette\Utils\Strings;
use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Class_\EntityTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Class_\TableTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Property_\ColumnTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Property_\CustomIdGeneratorTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Property_\GeneratedValueTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Property_\JoinTableTagValueNode;
use Rector\BetterPhpDocParser\Tests\PhpDocParser\AbstractPhpDocInfoTest;
use Rector\Core\Testing\StaticFixtureProvider;

final class DoctrineOrmTagNodeTest extends AbstractPhpDocInfoTest
{
    /**
     * @param class-string $expectedTagValueNodeType
     *
     * @dataProvider provideData()
     */
    public function test(string $filePath, string $expectedTagValueNodeType): void
    {
        if (Strings::endsWith($filePath, 'QuotesInNestedArray.php')) {
            $this->markTestSkipped('Quoting nested keys in annotations is in progress');
        }

        $this->doTestPrintedPhpDocInfo($filePath, $expectedTagValueNodeType);
    }

    public function provideData(): Iterator
    {
        $filePaths = StaticFixtureProvider::yieldFileFromDirectory(__DIR__ . '/Fixture/Property/Column', '*.php');
        foreach ($filePaths as $filePath) {
            yield [$filePath, ColumnTagValueNode::class];
        }

        $filePaths = StaticFixtureProvider::yieldFileFromDirectory(__DIR__ . '/Fixture/Property/JoinTable', '*.php');
        foreach ($filePaths as $filePath) {
            yield [$filePath, JoinTableTagValueNode::class];
        }

        $filePaths = StaticFixtureProvider::yieldFileFromDirectory(__DIR__ . '/Fixture/Class_/Entity', '*.php');
        foreach ($filePaths as $filePath) {
            yield [$filePath, EntityTagValueNode::class];
        }

        $filePaths = StaticFixtureProvider::yieldFileFromDirectory(__DIR__ . '/Fixture/Class_/Table', '*.php');
        foreach ($filePaths as $filePath) {
            yield [$filePath, TableTagValueNode::class];
        }

        $filePaths = StaticFixtureProvider::yieldFileFromDirectory(
            __DIR__ . '/Fixture/Property/CustomIdGenerator',
            '*.php'
        );
        foreach ($filePaths as $filePath) {
            yield [$filePath, CustomIdGeneratorTagValueNode::class];
        }

        $filePaths = StaticFixtureProvider::yieldFileFromDirectory(
            __DIR__ . '/Fixture/Property/GeneratedValue',
            '*.php'
        );
        foreach ($filePaths as $filePath) {
            yield [$filePath, GeneratedValueTagValueNode::class];
        }
    }
}
