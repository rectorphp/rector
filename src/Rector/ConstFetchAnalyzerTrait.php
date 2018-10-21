<?php declare(strict_types=1);

namespace Rector\Rector;

use PhpParser\Node;
use Rector\NodeAnalyzer\ConstFetchAnalyzer;

/**
 * This could be part of @see AbstractRector, but decopuling to trait
 * makes clear what code has 1 purpose.
 */
trait ConstFetchAnalyzerTrait
{
    /**
     * @var ConstFetchAnalyzer
     */
    private $constFetchAnalyzer;

    /**
     * @required
     */
    public function setConstFetchAnalyzer(ConstFetchAnalyzer $constFetchAnalyzer): void
    {
        $this->constFetchAnalyzer = $constFetchAnalyzer;
    }

    public function isFalse(Node $node): bool
    {
        return $this->constFetchAnalyzer->isFalse($node);
    }

    public function isTrue(Node $node): bool
    {
        return $this->constFetchAnalyzer->isTrue($node);
    }

    public function isBool(Node $node): bool
    {
        return $this->constFetchAnalyzer->isBool($node);
    }

    public function isNull(Node $node): bool
    {
        return $this->constFetchAnalyzer->isNull($node);
    }
}
