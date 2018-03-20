<?php declare(strict_types=1);

namespace Rector\Rector;

use PhpParser\Node;
use PHPUnit\Framework\TestCase;
use Rector\Node\Attribute;

abstract class AbstractPHPUnitRector extends AbstractRector
{
    protected function isInTestClass(Node $node): bool
    {
        $parentClassName = (string) $node->getAttribute(Attribute::PARENT_CLASS_NAME);

        return in_array($parentClassName, [TestCase::class, 'PHPUnit_Framework_TestCase'], true);
    }
}
