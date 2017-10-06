<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\UseUse;
use PhpParser\NodeVisitorAbstract;
use Rector\Node\Attribute;
use Rector\NodeTypeResolver\UseStatements;

final class NamespaceResolver extends NodeVisitorAbstract
{
    /**
     * @var UseStatements
     */
    private $useStatements;

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
        $this->useStatements = new UseStatements;
    }

    public function enterNode(Node $node): void
    {
        if ($node instanceof Namespace_) {
            $this->namespace = $node->name ? $node->name->toString() : '';
        }

        if ($node instanceof UseUse) {
            $this->useStatements->addUseStatement($node->name->toString());
        }

        $node->setAttribute(Attribute::NAMESPACE, $this->namespace);
        $node->setAttribute(Attribute::USE_STATEMENTS, $this->useStatements);
    }
}
