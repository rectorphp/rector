<?php

declare (strict_types=1);
namespace Rector\Core\Contract\Rector;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
interface ScopeAwarePhpRectorInterface extends \Rector\Core\Contract\Rector\PhpRectorInterface
{
    /**
     * Process Node of matched type with its PHPStan scope
     * @return Node|Node[]|null
     */
    public function refactorWithScope(Node $node, Scope $scope);
}
