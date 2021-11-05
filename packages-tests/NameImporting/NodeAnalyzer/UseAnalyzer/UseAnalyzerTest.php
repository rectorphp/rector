<?php

declare(strict_types=1);

namespace Rector\Tests\NameImporting\NodeAnalyzer\UseAnalyzer;

use Iterator;
use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use Rector\NameImporting\NodeAnalyzer\UseAnalyzer;
use Rector\NameImporting\ValueObject\NameAndParent;
use Rector\Testing\PHPUnit\AbstractTestCase;
use Rector\Testing\TestingParser\TestingParser;
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

        $namesAndParentsByShortName = $this->useAnalyzer->resolveUsedNameNodes($firstStmt);
        $this->assertArrayHasKey($expectedShortName, $namesAndParentsByShortName);

        $namesAndParents = $namesAndParentsByShortName[$expectedShortName];
        $this->assertArrayHasKey($position, $namesAndParents);

        $nameAndParent = $namesAndParents[$position];
        $this->assertInstanceOf(NameAndParent::class, $nameAndParent);

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
        yield [__DIR__ . '/Fixture/some_uses.php.inc', 'SomeUses', 0, new Identifier('SomeUses'), Class_::class];
    }
}
