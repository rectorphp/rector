<?php

declare (strict_types=1);
namespace Rector\Core\Rector;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use Rector\Core\Contract\Rector\ScopeAwarePhpRectorInterface;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\Node\AttributeKey;
abstract class AbstractScopeAwareRector extends \Rector\Core\Rector\AbstractRector implements ScopeAwarePhpRectorInterface
{
    /**
     * Process Node of matched type with its PHPStan scope
     * @return Node|Node[]|null
     */
    public function refactor(Node $node)
    {
        $scope = $node->getAttribute(AttributeKey::SCOPE);
        if ($this->scopeAnalyzer->isScopeResolvableFromFile($node, $scope)) {
            $smartFileInfo = $this->file->getSmartFileInfo();
            $scope = $this->scopeFactory->createFromFile($smartFileInfo);
            $this->changedNodeScopeRefresher->refresh($node, $scope, $smartFileInfo);
        }
        if (!$scope instanceof Scope) {
            $parent = $node->getAttribute(AttributeKey::PARENT_NODE);
            $errorMessage = \sprintf('Scope not available on "%s" node with parent node of "%s", but is required by a refactorWithScope() method of "%s" rule. Fix scope refresh on changed nodes first', \get_class($node), $parent instanceof Node ? \get_class($parent) : null, static::class);
            throw new ShouldNotHappenException($errorMessage);
        }
        return $this->refactorWithScope($node, $scope);
    }
}
