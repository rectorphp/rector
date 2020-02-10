<?php

declare(strict_types=1);

namespace Rector\Core\Rector\AbstractRector;

use Rector\NodeCollector\NodeFinder\ClassLikeParsedNodesFinder;
use Rector\NodeCollector\NodeFinder\FunctionLikeParsedNodesFinder;

/**
 * This could be part of @see AbstractRector, but decopuling to trait
 * makes clear what code has 1 purpose.
 */
trait NodeCollectorTrait
{
    /**
     * @var ClassLikeParsedNodesFinder
     */
    protected $classLikeParsedNodesFinder;

    /**
     * @var FunctionLikeParsedNodesFinder
     */
    protected $functionLikeParsedNodesFinder;

    /**
     * @required
     */
    public function autowireNodeCollectorTrait(
        ClassLikeParsedNodesFinder $classLikeParsedNodesFinder,
        FunctionLikeParsedNodesFinder $functionLikeParsedNodesFinder
    ): void {
        $this->classLikeParsedNodesFinder = $classLikeParsedNodesFinder;
        $this->functionLikeParsedNodesFinder = $functionLikeParsedNodesFinder;
    }
}
