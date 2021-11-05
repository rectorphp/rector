<?php

declare(strict_types=1);

namespace Rector\Tests\CodingStyle\Node\UseManipulator;

use Iterator;
use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use Rector\CodingStyle\Node\UseManipulator;
use Rector\CodingStyle\ValueObject\NameAndParent;
use Rector\Testing\PHPUnit\AbstractTestCase;
use Rector\Testing\TestingParser\TestingParser;
use Rector\Tests\CodingStyle\Node\UseManipulator\Source\FirstUsage;

final class UseManipulatorTest extends AbstractTestCase
{
    private UseManipulator $useManipulator;

    private TestingParser $testingParser;

    protected function setUp(): void
    {
        $this->boot();

        $this->useManipulator = $this->getService(UseManipulator::class);
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

        $namesAndParentsByShortName = $this->useManipulator->resolveUsedNameNodes($firstStmt);
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
