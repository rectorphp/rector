<?php

declare(strict_types=1);

namespace Rector\Symfony\Bridge\NodeAnalyzer;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\NodeTypeResolver\Node\AttributeKey;

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

        $parentClassName = (string) $node->getAttribute(AttributeKey::PARENT_CLASS_NAME);
        if (Strings::endsWith($parentClassName, 'Controller')) {
            return true;
        }

        if (Strings::endsWith((string) $node->name, 'Action')) {
            return true;
        }

        return $node->isPublic();
    }
}
