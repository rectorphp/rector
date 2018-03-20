<?php declare(strict_types=1);

namespace Rector\Rector;

use Nette\Utils\Strings;
use PhpParser\Node;
use Rector\Node\Attribute;

abstract class AbstractPHPUnitRector extends AbstractRector
{
    /**
     * Check if the file is a PHPUnit TestCase. By default, it should end with "Test", as it is the standard.
     *
     * @see https://phpunit.de/getting-started-with-phpunit.html
     */
    protected function isInTestClass(Node $node): bool
    {
        $className = (string) $node->getAttribute(Attribute::CLASS_NAME);

        return Strings::endsWith($className, 'Test');
    }
}
