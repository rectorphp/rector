<?php

declare(strict_types=1);

namespace Rector\Tests\NameImporting\NodeAnalyzer\UseAnalyzer;

use Iterator;
use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\UseUse;
use PhpParser\Node\UnionType;
use Rector\NameImporting\NodeAnalyzer\UseAnalyzer;
use Rector\NameImporting\ValueObject\NameAndParent;
use Rector\Testing\PHPUnit\AbstractTestCase;
use Rector\Testing\TestingParser\TestingParser;
use Rector\Tests\CodingStyle\Rector\Use_\RemoveUnusedAliasRector\Source\AbstractKernel;
use Rector\Tests\NameImporting\NodeAnalyzer\UseAnalyzer\Source\FirstUsage;

final class UseAnalyzerTest extends AbstractTestCase
{
    private UseAnalyzer $useAnalyzer;

    private TestingParser $testingParser;

    protected function setUp(): void
    {
        $this->boot();

        $this->useAnalyzer = $this->getService(UseAnalyzer::class);
        $this->testingParser = $this->getService(TestingParser::class);
    }

    /**
     * @dataProvider provideData()
     *
     * @param class-string<Node> $parentNodeType
     * @param Identifier|FullyQualified $expectedNameNode
     */
    public function test(
        string $filePath,
        string $expectedShortName,
        int $position,
        Name|Identifier $expectedNameNode,
        string $parentNodeType
    ): void {
        $file = $this->testingParser->parseFilePathToFile($filePath);
        $firstStmt = $file->getOldStmts()[1];

        $namesAndParents = $this->useAnalyzer->resolveUsedNameNodes($firstStmt);

        $this->assertArrayHasKey($position, $namesAndParents);
        $nameAndParent = $namesAndParents[$position];
        $this->assertInstanceOf(NameAndParent::class, $nameAndParent);

        $this->assertTrue($nameAndParent->matchShortName($expectedShortName));

        // remove attributes for compare
        $nameNode = $nameAndParent->getNameNode();
        $nameNode->setAttributes([]);

        $this->assertEquals($expectedNameNode, $nameNode);
        $this->assertInstanceOf($parentNodeType, $nameAndParent->getParentNode());
    }

    public function provideData(): Iterator
    {
        yield [
            __DIR__ . '/Fixture/some_uses.php.inc',
            'FirstUsage',
            0,
            new FullyQualified(FirstUsage::class),
            New_::class,
        ];

        yield [__DIR__ . '/Fixture/some_uses.php.inc', 'SomeUses', 1, new Identifier('SomeUses'), Class_::class];

        yield [
            __DIR__ . '/Fixture/use_import.php.inc',
            'BaseKernel',
            4,
            new FullyQualified(AbstractKernel::class),
            New_::class,
        ];

        yield [
            __DIR__ . '/Fixture/use_import.php.inc',
            'BaseInterface',
            6,
            new Identifier('BaseInterface'),
            UseUse::class,
        ];

        yield [
            __DIR__ . '/Fixture/use_import.php.inc',
            'BaseKernel',
            7,
            new Identifier('BaseKernel'),
            UseUse::class,
        ];

        yield [__DIR__ . '/Fixture/use_import.php.inc', 'SomeClass', 5, new Identifier('SomeClass'), Class_::class];

        yield [
            __DIR__ . '/Fixture/union_alias_imports.php.inc',
            'String_',
            0,
            new FullyQualified(String_::class),
            UnionType::class,
        ];

        yield [
            __DIR__ . '/Fixture/union_alias_imports.php.inc',
            'PhpParserUnionType',
            3,
            new Identifier('PhpParserUnionType'),
            UseUse::class,
        ];
    }
}
