<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\NodeVisitor\FunctionMethodAndClassNodeVisitor;

use Iterator;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use Rector\CodingStyle\Naming\ClassNaming;
use Rector\Core\Testing\PHPUnit\AbstractNodeVisitorTestCase;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeNameResolver\Regex\RegexPatternDetector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeVisitor\FunctionMethodAndClassNodeVisitor;

final class FunctionMethodAndClassNodeVisitorTest extends AbstractNodeVisitorTestCase
{
    /**
     * @dataProvider provideData();
     */
    public function testVisitor(string $file): void
    {
        $this->doTestFile($file);
    }

    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture', '*.php.inc');
    }

    /**
     * @param Node[] $nodes
     */
    protected function visitNodes(array $nodes): void
    {
        $nodeTraverser = new NodeTraverser();
        $nodeTraverser->addVisitor(new NameResolver());
        $nodeTraverser->addVisitor(
            new FunctionMethodAndClassNodeVisitor(new ClassNaming(new NodeNameResolver(new RegexPatternDetector())))
        );
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
