<?php

declare (strict_types=1);
namespace Rector\Rector;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PHPStan\Analyser\MutatingScope;
use PHPStan\Analyser\Scope;
use Rector\Contract\Rector\ScopeAwareRectorInterface;
use Rector\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\Node\AttributeKey;
abstract class AbstractScopeAwareRector extends \Rector\Rector\AbstractRector implements ScopeAwareRectorInterface
{
    /**
     * Process Node of matched type with its PHPStan scope
     * @return Node|Node[]|null|NodeTraverser::*
     */
    public function refactor(Node $node)
    {
        /** @var MutatingScope|null $currentScope */
        $currentScope = $node->getAttribute(AttributeKey::SCOPE);
        if (!$currentScope instanceof Scope) {
            $errorMessage = \sprintf('Scope not available on "%s" node, but is required by a refactorWithScope() method of "%s" rule. Fix scope refresh on changed nodes first', \get_class($node), static::class);
            throw new ShouldNotHappenException($errorMessage);
        }
        return $this->refactorWithScope($node, $currentScope);
    }
}
