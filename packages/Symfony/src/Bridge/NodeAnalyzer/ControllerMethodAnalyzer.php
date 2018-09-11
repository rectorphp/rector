<?php declare(strict_types=1);

namespace Rector\Symfony\Bridge\NodeAnalyzer;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\NodeTypeResolver\Node\Attribute;

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

        if (Strings::endsWith($parentClassName, 'Controller')) {
            return true;
        }

        /** @var Identifier $identifierNode */
        $identifierNode = $node->name;
        if (Strings::endsWith($identifierNode->toString(), 'Action')) {
            return true;
        }

        return $node->isPublic();
    }
}
