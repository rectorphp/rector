<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\PerNodeCallerTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use Rector\Node\Attribute;
use Rector\NodeTypeResolver\Contract\PerNodeCallerTypeResolver\PerNodeCallerTypeResolverInterface;

/**
 * This will tell the type of Node, which is calling this method
 *
 * E.g.:
 * - {parent}::callMe()
 * - {$this}::callMe()
 * - {self}::callMe()
 */
final class StaticCallCallerTypeResolver implements PerNodeCallerTypeResolverInterface
{
    /**
     * @param StaticCall $staticCallNode
     * @return string[]
     */
    public function resolve(Node $staticCallNode): array
    {
        $types = [];
        if ($staticCallNode->class instanceof Name) {
            if ($staticCallNode->class->getAttribute(Attribute::TYPES)) {
                $types = $staticCallNode->class->getAttribute(Attribute::TYPES);
            }

            $class = $staticCallNode->class->toString();
            if ($class === 'parent') {
                $types[] = $staticCallNode->class->getAttribute(Attribute::PARENT_CLASS_NAME);
            }

            $types = array_unique($types);
        }

        if ($staticCallNode->class instanceof Variable) {
            $types[] = $staticCallNode->class->getAttribute(Attribute::CLASS_NAME);
        }

        return $types;
    }

    /**
     * @return string[]
     */
    public function getNodeClasses(): array
    {
        return [StaticCall::class];
    }
}
