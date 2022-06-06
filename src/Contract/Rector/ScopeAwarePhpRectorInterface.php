<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Core\Contract\Rector;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PHPStan\Analyser\Scope;
interface ScopeAwarePhpRectorInterface extends PhpRectorInterface
{
    /**
     * Process Node of matched type with its PHPStan scope
     * @return Node|Node[]|null
     */
    public function refactorWithScope(Node $node, Scope $scope);
}
