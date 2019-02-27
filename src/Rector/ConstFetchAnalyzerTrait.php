<?php declare(strict_types=1);

namespace Rector\Rector;

use PhpParser\Node;
use Rector\PhpParser\Node\Manipulator\ConstFetchManipulator;

/**
 * This could be part of @see AbstractRector, but decopuling to trait
 * makes clear what code has 1 purpose.
 */
trait ConstFetchAnalyzerTrait
{
    /**
     * @var ConstFetchManipulator
     */
    private $constFetchManipulator;

    /**
     * @required
     */
    public function setConstFetchAnalyzer(ConstFetchManipulator $constFetchManipulator): void
    {
        $this->constFetchManipulator = $constFetchManipulator;
    }

    public function isFalse(Node $node): bool
    {
        return $this->constFetchManipulator->isFalse($node);
    }

    public function isTrue(Node $node): bool
    {
        return $this->constFetchManipulator->isTrue($node);
    }

    public function isBool(Node $node): bool
    {
        return $this->constFetchManipulator->isBool($node);
    }

    public function isNull(Node $node): bool
    {
        return $this->constFetchManipulator->isNull($node);
    }
}
