<?php declare(strict_types=1);

namespace Rector\NodeModifier;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use Rector\Exception\InvalidNodeTypeException;

final class VisibilityModifier
{
    /**
     * This way "abstract", "static", "final" are kept
     *
     * @param ClassMethod|Property $node
     */
    public function removeOriginalVisibilityFromFlags($node): void
    {
        $this->ensureIsClassMethodOrProperty($node, __METHOD__);

        if ($node->isPublic()) {
            $node->flags -= Class_::MODIFIER_PUBLIC;
        }

        if ($node->isProtected()) {
            $node->flags -= Class_::MODIFIER_PROTECTED;
        }

        if ($node->isPrivate()) {
            $node->flags -= Class_::MODIFIER_PRIVATE;
        }
    }

    private function ensureIsClassMethodOrProperty(Node $node, string $location): void
    {
        if ($node instanceof ClassMethod || $node instanceof Property) {
            return;
        }

        throw new InvalidNodeTypeException(sprintf(
            '"%s" only accepts "%s" types. "%s" given.',
            $location,
            implode('", "', [ClassMethod::class, Property::class]),
            get_class($node)
        ));
    }
}
