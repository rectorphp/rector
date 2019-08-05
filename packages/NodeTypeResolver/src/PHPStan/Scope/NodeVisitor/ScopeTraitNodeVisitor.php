<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\PHPStan\Scope\NodeVisitor;

use Closure;
use PhpParser\Node;
use PhpParser\Node\Stmt\Trait_;
use PhpParser\NodeVisitorAbstract;
use PHPStan\Analyser\NodeScopeResolver;
use PHPStan\Analyser\Scope;
use Rector\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * Adds scope to all nodes inside trait even without class that is using it (that what PHPStan needs to add a scope to them)
 */
final class ScopeTraitNodeVisitor extends NodeVisitorAbstract
{
    /**
     * @var NodeScopeResolver
     */
    private $nodeScopeResolver;

    /**
     * @var Closure
     */
    private $nodeCallback;

    public function __construct(NodeScopeResolver $nodeScopeResolver)
    {
        $this->nodeScopeResolver = $nodeScopeResolver;
    }

    public function setNodeCallback(Closure $nodeCallback): void
    {
        $this->nodeCallback = $nodeCallback;
    }

    public function enterNode(Node $node): ?Node
    {
        if ($this->nodeCallback === null) {
            throw new ShouldNotHappenException(sprintf(
                'Set "$nodeCallback" property via "setNodeCallback()" on "%s" first',
                self::class
            ));
        }

        if (! $node instanceof Trait_) {
            return null;
        }

        /** @var Scope|null $traitScope */
        $traitScope = $node->getAttribute(AttributeKey::SCOPE);
        if ($traitScope === null) {
            throw new ShouldNotHappenException(sprintf('A trait "%s" is missing a scope', (string) $node->name));
        }

        $this->nodeScopeResolver->processStmtNodes($node, $node->stmts, $traitScope, $this->nodeCallback);

        return $node;
    }
}
