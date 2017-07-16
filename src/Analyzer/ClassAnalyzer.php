<?php declare(strict_types=1);

namespace Rector\Analyzer;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;

final class ClassAnalyzer
{
    public function isControllerClassNode(Node $node): bool
    {
        if (! $node instanceof Class_) {
            return false;
        }

        if ($node->extends instanceof Name) {
            return Strings::endsWith($node->extends->getLast(), 'Controller');
        }

        return false;
    }

    public function isContainerAwareClassNode(Node $node): bool
    {
        if (! $node instanceof Class_) {
            return false;
        }

        foreach ($node->implements as $nameNode) {
            if (Strings::endsWith($nameNode->getLast(), 'ContainerAwareInterface')) {
                return true;
            }
        }

        return false;
    }
}
