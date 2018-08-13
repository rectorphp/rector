<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Metadata;

use PhpParser\Node;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Use_;
use Rector\NodeTypeResolver\Contract\Metadata\NodeDecoratorInterface;
use Rector\NodeTypeResolver\Node\MetadataAttribute;
use Rector\Utils\BetterNodeFinder;

final class NamespaceNodeDecorator implements NodeDecoratorInterface
{
    /**
     * @var string|null
     */
    private $namespaceName;

    /**
     * @var Namespace_|null
     */
    private $namespaceNode;

    /**
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;

    /**
     * @var Use_[]
     */
    private $useNodes = [];

    public function __construct(BetterNodeFinder $betterNodeFinder)
    {
        $this->betterNodeFinder = $betterNodeFinder;
    }

    public function reset(): void
    {
        $this->namespaceName = null;
        $this->namespaceNode = null;
        $this->useNodes = [];
    }

    public function decorateNode(Node $node): void
    {
        if ($node instanceof Namespace_) {
            $this->namespaceName = $node->name ? $node->name->toString() : null;
            $this->namespaceNode = $node;
            $this->useNodes = $this->betterNodeFinder->findInstanceOf($node, Use_::class);
        }

        $node->setAttribute(MetadataAttribute::NAMESPACE_NAME, $this->namespaceName);
        $node->setAttribute(MetadataAttribute::NAMESPACE_NODE, $this->namespaceNode);
        $node->setAttribute(MetadataAttribute::USE_NODES, $this->useNodes);
    }
}
