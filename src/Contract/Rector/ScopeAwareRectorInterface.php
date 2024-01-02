<?php

declare (strict_types=1);
namespace Rector\Contract\Rector;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PHPStan\Analyser\Scope;
interface ScopeAwareRectorInterface extends \Rector\Contract\Rector\RectorInterface
{
    /**
     * Process Node of matched type with its PHPStan scope
     * @return Node|Node[]|null|NodeTraverser::*
     */
    public function refactorWithScope(Node $node, Scope $scope);
}
