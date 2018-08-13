<?php declare(strict_types=1);

namespace Rector\Symfony\Bridge\NodeAnalyzer;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\NodeTypeResolver\Node\MetadataAttribute;

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

        $parentClassName = $node->getAttribute(MetadataAttribute::PARENT_CLASS_NAME);
        if (! Strings::endsWith($parentClassName, 'Controller')) {
            return false;
        }

        /** @var Identifier $identifierNode */
        $identifierNode = $node->name;

        return Strings::endsWith($identifierNode->toString(), 'Action');
    }
}
