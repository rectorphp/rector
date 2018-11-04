<?php declare(strict_types=1);

namespace Rector\Rector;

use PhpParser\Node;
use Rector\PhpParser\Node\Maintainer\ConstFetchMaintainer;

/**
 * This could be part of @see AbstractRector, but decopuling to trait
 * makes clear what code has 1 purpose.
 */
trait ConstFetchAnalyzerTrait
{
    /**
     * @var ConstFetchMaintainer
     */
    private $constFetchMaintainer;

    /**
     * @required
     */
    public function setConstFetchAnalyzer(ConstFetchMaintainer $constFetchMaintainer): void
    {
        $this->constFetchMaintainer = $constFetchMaintainer;
    }

    public function isFalse(Node $node): bool
    {
        return $this->constFetchMaintainer->isFalse($node);
    }

    public function isTrue(Node $node): bool
    {
        return $this->constFetchMaintainer->isTrue($node);
    }

    public function isBool(Node $node): bool
    {
        return $this->constFetchMaintainer->isBool($node);
    }

    public function isNull(Node $node): bool
    {
        return $this->constFetchMaintainer->isNull($node);
    }
}
