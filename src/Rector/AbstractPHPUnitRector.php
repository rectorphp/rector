<?php declare(strict_types=1);

namespace Rector\Rector;

use Nette\Utils\Strings;
use PhpParser\Node;
use Rector\Node\Attribute;

abstract class AbstractPHPUnitRector extends AbstractRector
{
    protected function isInTestClass(Node $node): bool
    {
        $className = $node->getAttribute(Attribute::CLASS_NAME);

        return Strings::endsWith((string) $className, 'Test');
    }
}
