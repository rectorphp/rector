<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\NodeVisitor\FunctionMethodAndClassNodeVisitor;

use Iterator;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeVisitor\FunctionMethodAndClassNodeVisitor;
use Rector\Testing\PHPUnit\AbstractNodeVisitorTestCase;

final class FunctionMethodAndClassNodeVisitorTest extends AbstractNodeVisitorTestCase
{
    public function provideDataForTest(): Iterator
    {
        yield [__DIR__ . '/fixtures/simple.php.inc'];
        yield [__DIR__ . '/fixtures/anonymousClass.php.inc'];
    }

    /**
     * @dataProvider provideDataForTest();
     */
    public function testVisitor(string $file): void
    {
        $this->doTestFile($file);
    }

    /**
     * @param Node[] $nodes
     */
    protected function visitNodes(array $nodes): void
    {
        $nodeTraverser = new NodeTraverser();
        $nodeTraverser->addVisitor(new NameResolver());
        $nodeTraverser->addVisitor(new FunctionMethodAndClassNodeVisitor());
        $nodeTraverser->traverse($nodes);
    }

    /**
     * @return string[]
     */
    protected function getRelevantAttributes(): array
    {
        return [AttributeKey::CLASS_NAME, AttributeKey::CLASS_NODE, AttributeKey::METHOD_NAME];
    }
}
