<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\NodeVisitorAbstract;
use Rector\Node\Attribute;

final class NamespaceResolver extends NodeVisitorAbstract
{
    /**
     * @var string[]
     */
    private $useStatements = [];

    /**
     * @var string|null
     */
    private $namespace;

    /**
     * @param Node[] $nodes
     */
    public function beforeTraverse(array $nodes): void
    {
        $this->namespace = null;
        $this->useStatements = [];
    }

    public function enterNode(Node $node): void
    {
        if ($node instanceof Namespace_) {
            $this->namespace = $node->name->toString();
        }

        if ($node instanceof Use_) {
            $this->useStatements[] = $node->uses[0]->name->toString();
        }

        $node->setAttribute(Attribute::NAMESPACE, $this->namespace);
        $node->setAttribute(Attribute::USE_STATEMENTS, $this->useStatements);
    }
}
