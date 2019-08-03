<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\PHPStan\Scope\NodeVisitor;

use Closure;
use PhpParser\Node;
use PhpParser\Node\Stmt\Trait_;
use PhpParser\NodeVisitorAbstract;
use PHPStan\Analyser\NodeScopeResolver as PHPStanNodeScopeResolver;
use Rector\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\PackageBuilder\Reflection\PrivatesCaller;

/**
 * Adds scope to all nodes inside trait even without class that is using it (that what PHPStan needs to add a scope to them)
 */
final class ScopeTraitNodeVisitor extends NodeVisitorAbstract
{
    /**
     * @var PHPStanNodeScopeResolver
     */
    private $phpStanNodeScopeResolver;

    /**
     * @var Closure
     */
    private $nodeCallback;

    /**
     * @var PrivatesCaller
     */
    private $privatesCaller;

    public function __construct(PHPStanNodeScopeResolver $phpStanNodeScopeResolver, PrivatesCaller $privatesCaller)
    {
        $this->phpStanNodeScopeResolver = $phpStanNodeScopeResolver;
        $this->privatesCaller = $privatesCaller;
    }

    public function setNodeCallback(Closure $nodeCallback): void
    {
        $this->nodeCallback = $nodeCallback;
    }

    public function enterNode(Node $node)
    {
        if ($this->nodeCallback === null) {
            throw new ShouldNotHappenException();
        }

        if (! $node instanceof Trait_) {
            return null;
        }

        $traitScope = $node->getAttribute(AttributeKey::SCOPE);

        $this->privatesCaller->callPrivateMethod(
            $this->phpStanNodeScopeResolver,
            'processStmtNodes',
            $node,
            $node->stmts,
            $traitScope,
            $this->nodeCallback
        );

        return $node;
    }
}
