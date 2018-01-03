<?php declare(strict_types=1);

namespace Rector\Scoper;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Rector\Node\Attribute;

final class Scoper
{
    /**
     * Possible PHPUnit base test clases
     *
     * @var string[]
     */
    private $phpUnitTestClasses = ['PHPUnit\Framework\TestCase', 'PHPUnit_Framework_TestCase'];

    public function isInClassOfPhpUnitTestCase(Node $node): bool
    {
        /** @var Class_ $classNode */
        $classNode = $node->getAttribute(Attribute::CLASS_NODE);
        if ($classNode === null) {
            return false;
        }

        // class ends with "*Test"
        $className = $classNode->getAttribute(Attribute::CLASS_NAME);
        if (Strings::endsWith($className, 'Test')) {
            return false;
        }

        // extends test case directly
        $parentClassName = $classNode->getAttribute(Attribute::PARENT_CLASS_NAME);
        if (in_array($parentClassName, $this->phpUnitTestClasses, true)) {
            return true;
        }

        // extends test case directly
        $parentClassName = $classNode->getAttribute(Attribute::PARENT_CLASS_NAME);
        if (in_array($parentClassName, $this->phpUnitTestClasses, true)) {
            return true;
        }

        // extends abstract test case
        if (Strings::endsWith($parentClassName, 'TestCase')) {
            return true;
        }

        // dunno?
        return false;
    }

    public function isInClassScope(Node $node, string $class): void
    {
        // better reflection...
        dump($node, $class);
        // node->getAttribute(ClassNode...
        // node->getAttribute(ClassNode...->get name
        // node->getAttribute(ClassNode...->get parent names...
        die;
    }
}
