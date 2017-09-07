<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Property;
use PhpParser\NodeVisitorAbstract;
use Rector\Node\Attribute;

/**
 * In class, in interface, in trait, in method or in function?
 */
final class ScopeResolver extends NodeVisitorAbstract
{
    /**
     * @var string|null
     */
    private $currentScope;

    public function beforeTraverse(array $nodes): void
    {
        $this->currentScope = null;
    }

    public function enterNode(Node $node): void
    {
        if ($node instanceof Function_) {
            $this->currentScope = 'scope_function';
        }

        if (($node instanceof Class_ && $node->isAnonymous()) || $node instanceof Property) {
            $this->currentScope = 'scope_class';
        }

        if ($node instanceof Interface_) {
            $this->currentScope = 'scope_interface';
        }

        if ($node instanceof ClassMethod) {
            $this->currentScope = 'scope_class_method';
        }

        if ($this->currentScope) {
            $node->setAttribute(Attribute::SCOPE, $this->currentScope);
        }
    }
}
