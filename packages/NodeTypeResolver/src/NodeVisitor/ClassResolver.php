<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\NodeVisitorAbstract;
use Rector\Node\Attribute;
use Rector\NodeTypeResolver\NodeAnalyzer\NamespaceAnalyzer;

/**
 * Add attribute 'class' with current class name.
 * Add 'use_imports' with all related uses.
 */
final class ClassResolver extends NodeVisitorAbstract
{
    /**
     * @var ClassLike|null
     */
    private $classNode;

    /**
     * @var NamespaceAnalyzer
     */
    private $namespaceAnalyzer;

    /**
     * @var string[]
     */
    private $useStatements = [];

    public function __construct(NamespaceAnalyzer $namespaceAnalyzer)
    {
        $this->namespaceAnalyzer = $namespaceAnalyzer;
    }

    /**
     * @param Node[] $nodes
     */
    public function beforeTraverse(array $nodes): void
    {
        $this->classNode = null;
    }

    public function enterNode(Node $node): void
    {
        // detect only first ClassLike elemetn
        if ($this->classNode === null && $node instanceof ClassLike) {
            // skip possible anonymous classes
            if ($node instanceof Class_ && $node->isAnonymous()) {
                return;
            }

            $this->classNode = $node;
            $this->useStatements = $this->namespaceAnalyzer->detectInClass($node);
        }

        if ($this->classNode) {
            $node->setAttribute(Attribute::CLASS_NODE, $this->classNode);
            $node->setAttribute(Attribute::CLASS_NAME, $this->classNode->namespacedName->toString());
            $node->setAttribute(Attribute::USE_STATEMENTS, $this->useStatements);
        }
    }
}
