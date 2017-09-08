<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\Trait_;
use PhpParser\NodeVisitorAbstract;
use Rector\Node\Attribute;

/**
 * In class, in interface, in trait, in method or in function.
 */
final class ScopeResolver extends NodeVisitorAbstract
{
    /**
     * @var string|null
     */
    private $currentScope;

    /**
     * @var Node|null
     */
    private $currentScopeNode;

    /**
     * @param Node[] $nodes
     */
    public function beforeTraverse(array $nodes): void
    {
        $this->currentScope = null;
    }

    public function enterNode(Node $node): void
    {
        $this->resolveClassLikeScope($node);

        if ($node instanceof Namespace_) {
            $this->currentScope = 'scope_namespace';
            $this->currentScopeNode = $node;
        }

        if ($node instanceof Function_) {
            $this->currentScope = 'scope_function';
            $this->currentScopeNode = $node;
        }

        if ($node instanceof ClassMethod) {
            $this->currentScope = 'scope_class_method';
            $this->currentScopeNode = $node;
        }

        if ($this->currentScope) {
            $node->setAttribute(Attribute::SCOPE, $this->currentScope);
            $node->setAttribute(Attribute::SCOPE_NODE, $this->currentScopeNode);
        }
    }

    public function leaveNode(Node $node): void
    {
        if ($node instanceof ClassLike) {
            if ($node instanceof Class_ && $node->isAnonymous()) {
                return;
            }

            $this->currentScope = null;
        }

        if ($node instanceof ClassMethod || $node instanceof Function_) {
            $this->currentScope = null;
        }
    }

    private function resolveClassLikeScope(Node $node): void
    {
        if (($node instanceof Class_ && $node->isAnonymous()) || $node instanceof Property) {
            $this->currentScope = 'scope_class';
            $this->currentScopeNode = $node;
        }

        if ($node instanceof Interface_) {
            $this->currentScope = 'scope_interface';
            $this->currentScopeNode = $node;
        }

        if ($node instanceof Trait_) {
            $this->currentScope = 'scope_trait';
            $this->currentScopeNode = $node;
        }
    }
}
