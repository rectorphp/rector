<?php declare(strict_types=1);

namespace Rector\NodeAnalyzer\Contrib\Symfony;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Node\Attribute;

final class ControllerMethodAnalyzer
{
    /**
     * Detect if is <some>Action() in Controller
     */
    public function isAction(Node $node): bool
    {
        if (! $node instanceof ClassMethod) {
            return false;
        }

        $parentClassName = $node->getAttribute(Attribute::PARENT_CLASS_NAME);
        $controllerClass = 'Symfony\Bundle\FrameworkBundle\Controller\Controller';

        if ($parentClassName !== $controllerClass) {
            return false;
        }

        return Strings::endsWith($node->name->toString(), 'Action');
    }
}
