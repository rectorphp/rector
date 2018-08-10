<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\NodeVisitor;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use Rector\NodeTypeResolver\Metadata\ClassAndMethodNodeDecorator;
use Rector\NodeTypeResolver\Metadata\NamespaceNodeDecorator;

final class MetadataNodeVisitor extends NodeVisitorAbstract
{
    /**
     * @var ClassAndMethodNodeDecorator
     */
    private $classAndMethodNodeDecorator;

    /**
     * @var NamespaceNodeDecorator
     */
    private $namespaceNodeDecorator;

    public function __construct(
        ClassAndMethodNodeDecorator $classAndMethodNodeDecorator,
        NamespaceNodeDecorator $namespaceNodeDecorator
    ) {
        $this->classAndMethodNodeDecorator = $classAndMethodNodeDecorator;
        $this->namespaceNodeDecorator = $namespaceNodeDecorator;
    }

    /**
     * @param Node[] $nodes
     */
    public function beforeTraverse(array $nodes): void
    {
        $this->classAndMethodNodeDecorator->reset();
        $this->namespaceNodeDecorator->reset();
    }

    public function enterNode(Node $node): void
    {
        $this->classAndMethodNodeDecorator->decorateNode($node);
        $this->namespaceNodeDecorator->decorateNode($node);
    }
}
