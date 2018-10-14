<?php declare(strict_types=1);

namespace Rector\Rector;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Rector\NodeTypeResolver\Node\Attribute;

abstract class AbstractPHPUnitRector extends AbstractRector
{
    protected function isInTestClass(Node $node): bool
    {
        /** @var Class_|null $classNode */
        $classNode = $node->getAttribute(Attribute::CLASS_NODE);
        if ($classNode === null) {
            return false;
        }

        return $this->isTypes($classNode, ['PHPUnit\Framework\TestCase', 'PHPUnit_Framework_TestCase']);
    }
}
